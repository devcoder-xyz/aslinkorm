<?php

namespace AlphaSoft\AsLinkOrm\Command;

use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\Mapping\Entity\Column;
use AlphaSoft\AsLinkOrm\Mapping\Entity\JoinColumn;
use AlphaSoft\AsLinkOrm\Mapping\Entity\OneToMany;
use AlphaSoft\AsLinkOrm\Mapping\Entity\PrimaryKeyColumn;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'aslinkorm:make:accessors')]
final class MakeAccessorsCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription('Generate getters and setters for an existing entity')
            ->setHelp('This command allows you to automatically generate getter and setter methods for the properties of an existing entity.')
            ->addArgument('entityName', InputArgument::REQUIRED, 'The name of the entity, ex : App\\Entity\\User')
            ->addOption('getter', 'g', InputOption::VALUE_NONE, 'Generate getters')
            ->addOption('setter', 's', InputOption::VALUE_NONE, 'Generate setters');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $entityName = $input->getArgument('entityName');
        $generateGetters = $input->getOption('getter');
        $generateSetters = $input->getOption('setter');

        if ($generateGetters === false && $generateSetters === false) {
            $io->error('You must specify at least one of --getter or --setter.');
            return Command::FAILURE;
        }

        if (!class_exists($entityName)) {
            $io->error(sprintf('The entity class "%s" does not exist. Please check the class name and try again.', $entityName));
            return Command::FAILURE;
        }

        if (!is_subclass_of($entityName, AsEntity::class)) {
            $io->error(sprintf('The class "%s" is not a valid subclass of "%s". Please check the class name and ensure it extends "%s".', $entityName, AsEntity::class, AsEntity::class));
            return Command::FAILURE;
        }
        $missing = [];
        $columns = $entityName::getColumns();

        foreach ($columns as $column) {
            if ($column instanceof PrimaryKeyColumn) {
                continue;
            }

            $getterName = $this->generateGetterColumnName($column);
            if ($generateGetters && !method_exists($entityName, $getterName)) {
                $missing[] = $this->getterColumnTemplate($column, $getterName);
            }

            $setterName = $this->generateSetterColumnName($column);
            if ($generateSetters && !method_exists($entityName, $setterName)) {
                $missing[] = $this->setterColumnTemplate($column, $setterName);
            }
        }


        $relations = $entityName::getOneToManyRelations();
        foreach ($relations as $relation) {
            $getterName = $this->generateGetterOneToManyRelationName($relation);

            if ($generateGetters && !method_exists($entityName, $getterName)) {
                $missing[] = $this->getterOneToManyTemplate($columns, $relation, $getterName);
            }
        }

        $this->updateClassCode($entityName, $missing);

        $io->success('success');

        return Command::SUCCESS;
    }

    private function updateClassCode(string $entityName, array $missing): void
    {
        $reflectionClass = new \ReflectionClass($entityName);
        $classCode = file_get_contents($reflectionClass->getFileName());
        $lastBracePosition = strrpos($classCode, '}');

        if ($lastBracePosition !== false) {
            $modifiedClassCode = substr_replace($classCode, implode(PHP_EOL, $missing) . PHP_EOL . PHP_EOL, $lastBracePosition, 0);
            file_put_contents($reflectionClass->getFileName(), $modifiedClassCode);
        }
    }

    private function generateGetterOneToManyRelationName(OneToMany $relation): string
    {
        $property = $relation->getShortName();
        return 'get' . ucfirst(self::snakeCaseToCamelCase($property)).'s';
    }

    private function generateGetterColumnName(Column $column): string
    {
        $property = $column->getProperty();
        if ($column instanceof JoinColumn) {
            $property = $column->getShortName();
        }

        return 'get' . ucfirst(self::snakeCaseToCamelCase($property));
    }

    private function generateSetterColumnName(Column $column): string
    {
        $property = $column->getProperty();
        if ($column instanceof JoinColumn) {
            $property = $column->getShortName();
        }


        return 'set' . ucfirst(self::snakeCaseToCamelCase($property));
    }

    private function setterColumnTemplate(Column $column, string $setterName): string
    {
        $property = $column->getProperty();
        $type = $column->getType();
        $variable = self::snakeCaseToCamelCase($property);
        $value = $variable;
        if ($column instanceof JoinColumn) {
            $variable = self::snakeCaseToCamelCase($column->getShortName());
            $value = $variable;
            $value .= '->getPrimaryKeyValue()';
        }

        return <<<PHP
    public function $setterName(?$type \$$variable): self
    {
        \$this->set('$property', \$$value);
        return \$this;
    }
PHP;
    }

    private function getterColumnTemplate(Column $column, string $getterName): string
    {
        if ($column instanceof JoinColumn) {
            return $this->getterJoinColumnTemplate($column,$getterName);
        }

        $property = $column->getProperty();
        $type = $column->getType();
        return <<<PHP
    public function $getterName(): ?$type
    {
        return \$this->get('$property');
    }
PHP;
    }

    private function getterJoinColumnTemplate(JoinColumn $column, string $getterName): string
    {
        $property = $column->getProperty();
        $type = $column->getType();
        $targetEntity = '\\'.ltrim($column->getTargetEntity(), '\\');;
        $referencedColumnName = $column->getReferencedColumnName();
        return <<<PHP
    public function $getterName(): ?$type
    {
        return \$this->hasOne($targetEntity::class, ['$referencedColumnName' => \$this->get('$property')]);
    }
PHP;
    }

    /**
     * @param array<Column> $columns
     * @param OneToMany $relation
     * @param string $getterName
     * @return string
     */
    private function getterOneToManyTemplate(array $columns, OneToMany $relation, string $getterName): string
    {
        $type = $relation->getType();
        $targetEntity = '\\'.ltrim($relation->getTargetEntity(), '\\');
        $criteria = $relation->getCriteria();

        $criteriaString = '[';
        foreach ($criteria as $referencedColumnName => $value) {

            foreach ($columns as $column) {
                if ($value === $column->getProperty()) {
                    if ($column instanceof PrimaryKeyColumn) {
                        $criteriaString .= "'$referencedColumnName' => \$this->getPrimaryKeyValue(), ";
                    }else {
                        $criteriaString .= "'$referencedColumnName' => \$this->get('$value'), ";
                    }
                    continue 2;
                }
            }
            $criteriaString .= "'$referencedColumnName' => '$value', ";
        }

        $criteriaString = rtrim($criteriaString, ', ') . ']';

        return <<<PHP

    public function $getterName(): $type
    {
        return \$this->hasMany($targetEntity::class, $criteriaString);
    }
PHP;
    }

    private static function snakeCaseToCamelCase(string $snakeCaseString): string
    {
        return lcfirst(str_replace('_', '', ucwords($snakeCaseString, '_')));
    }
}
