<?php

require 'vendor/autoload.php';
class CodeGenerator
{
    public static function generateGettersAndSetters(string $className)
    {
        /**
         * @var \AlphaSoft\AsLinkOrm\Mapping\Entity\Column $column
         */

        $columns = $className::getColumns();
        $missingGettersSetters = [];
        foreach ($columns as $column) {
            $propertyName = $column->getProperty();
            $getterName = 'get' . ucfirst(self::snakeCaseToCamelCase($propertyName));
            if ($column instanceof \AlphaSoft\AsLinkOrm\Mapping\Entity\JoinColumn) {
                $targetEntity = $column->getTargetEntity();
                $shortName =  (new \ReflectionClass($targetEntity))->getShortName();
                $getterName = 'get' . ucfirst(self::snakeCaseToCamelCase($shortName));
            }
            $setterName = 'set' . ucfirst(self::snakeCaseToCamelCase($propertyName));

            if (!method_exists($className, $getterName)) {

                if ($column instanceof \AlphaSoft\AsLinkOrm\Mapping\Entity\JoinColumn) {
                    $targetEntity = $column->getTargetEntity();
                    $referencedColumnName = $column->getReferencedColumnName();
                    $shortName =  (new \ReflectionClass($targetEntity))->getShortName();
                    $missingGettersSetters[] = "    public function $getterName(): ?$shortName
    {
        return \$this->hasOne($targetEntity::class, ['$referencedColumnName' => \$this->get('$propertyName')]);
    }";
                }else {
                    $missingGettersSetters[] = "    public function $getterName()\n    {\n       return \$this->get('$propertyName');\n    }\n";
                }
            }

            if (!method_exists($className, $getterName) && !$column instanceof \AlphaSoft\AsLinkOrm\Mapping\Entity\PrimaryKeyColumn) {
                $missingGettersSetters[] = "    public function $setterName(\$value): self\n    {\n        \$this->set('$propertyName', \$value);\n        return \$this;\n    }\n";
            }
        }

        $a = new \ReflectionClass($className);
        $classCode = file_get_contents($a->getFileName());
        $lastBracePosition = strrpos($classCode, '}');
        if ($lastBracePosition !== false) {
            $modifiedClassCode = substr_replace($classCode, implode("\n", $missingGettersSetters), $lastBracePosition, 0);
            file_put_contents($a->getFileName(), $modifiedClassCode);
        }
    }

    private static function snakeCaseToCamelCase($snakeCaseString)
    {
        return lcfirst(str_replace('_', '', ucwords($snakeCaseString, '_')));
    }
}

CodeGenerator::generateGettersAndSetters(\Test\AlphaSoft\AsLinkOrm\Model\Post::class);
