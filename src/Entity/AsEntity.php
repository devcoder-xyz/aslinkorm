<?php

namespace AlphaSoft\AsLinkOrm\Entity;

use AlphaSoft\AsLinkOrm\Cache\ColumnCache;
use AlphaSoft\AsLinkOrm\Cache\PrimaryKeyColumnCache;
use AlphaSoft\AsLinkOrm\EntityManager;
use AlphaSoft\AsLinkOrm\Mapping\Entity\Column;
use AlphaSoft\AsLinkOrm\Mapping\Entity\Entity;
use AlphaSoft\AsLinkOrm\Mapping\Entity\PrimaryKeyColumn;
use AlphaSoft\DataModel\Model;
use LogicException;
use SplObjectStorage;

abstract class AsEntity extends Model
{
    private ?\AlphaSoft\AsLinkOrm\EntityManager $__manager = null;

    private array $_modifiedAttributes = [];

    final public function set(string $property, $value): Model
    {
        parent::set($property, $value);
        $this->_modifiedAttributes[$property] = $value;
        return $this;
    }

    final public function toDb(): array
    {
        $dbData = [];
        foreach (self::getColumns() as $column) {
            $property = $column->getProperty();
            if (!array_key_exists($property, $this->attributes)) {
                continue;
            }
            $dbData[sprintf('`%s`', $column->getName())] = $this->attributes[$property];
        }
        return $dbData;
    }

    final public function toDbForUpdate(): array
    {
        $dbData = [];
        foreach (self::getColumns() as $column) {
            $property = $column->getProperty();
            if (!array_key_exists($property, $this->_modifiedAttributes)) {
                continue;
            }
            $dbData[sprintf('`%s`', $column->getName())] = $this->_modifiedAttributes[$property];
        }
        return $dbData;
    }

    public function setEntityManager(?EntityManager $manager): void
    {
        $this->__manager = $manager;
    }

    protected function hasOne(string $relatedModel, array $criteria = []): ?object
    {
        if (!is_subclass_of($relatedModel, AsEntity::class)) {
            throw new LogicException("The related model '$relatedModel' must be a subclass of AsEntity.");
        }

        return $this->getEntityManager()->getRepository($relatedModel::getRepositoryName())->findOneBy($criteria);
    }

    protected function hasMany(string $relatedModel, array $criteria = []): SplObjectStorage
    {
        if (!is_subclass_of($relatedModel, AsEntity::class)) {
            throw new LogicException("The related model '$relatedModel' must be a subclass of AsEntity.");
        }

        return $this->getEntityManager()->getRepository($relatedModel::getRepositoryName())->findBy($criteria);
    }

    /**
     * @return EntityManager|null
     */
    private function getEntityManager(): ?EntityManager
    {
        if ($this->__manager === null) {
            throw new LogicException(EntityManager::class . ' must be set before using this method.');
        }
        return $this->__manager;
    }

    final static protected function getDefaultAttributes(): array
    {
        $attributes = [];
        foreach (self::getColumns() as $column) {
            $attributes[$column->getProperty()] = $column->getDefaultValue();
        }
        return $attributes;
    }

    final static protected function getDefaultColumnMapping(): array
    {
        $columns = [];
        foreach (self::getColumns() as $column) {
            $columns[$column->getProperty()] = $column->getName();
        }
        return $columns;
    }

    final static public function getPrimaryKeyColumn(): string
    {
        $cache = PrimaryKeyColumnCache::getInstance();
        if (!$cache->get(static::class) instanceof \AlphaSoft\AsLinkOrm\Mapping\Entity\PrimaryKeyColumn) {

            $columnsFiltered = array_filter(self::getColumns(), fn(Column $column): bool => $column instanceof PrimaryKeyColumn);

            if (count($columnsFiltered) === 0) {
                throw new LogicException('At least one primary key is required.');
            }

            if (count($columnsFiltered) > 1) {
                throw new LogicException('Only one primary key is allowed.');
            }

            $primaryKey = $columnsFiltered[0];

            $cache->set(static::class, $primaryKey);
        }
        return $cache->get(static::class)->getName();
    }

    /**
     * @return array<Column>
     */
    final static public function getColumns(): array
    {
        $cache = ColumnCache::getInstance();
        if (empty($cache->get(static::class))) {
            $cache->set(static::class, static::columnsMapping());
        }
        return $cache->get(static::class);
    }

    static public function getTable(): string
    {
        $reflector = new \ReflectionClass(static::class);
        $attributes = $reflector->getAttributes(Entity::class);

        $table = $attributes[0]->getArguments()['table'] ?? null;
        if ($table === null) {
            throw new LogicException('table is required');
        }
        return $table;
    }

    static public function getRepositoryName(): string
    {
        $reflector = new \ReflectionClass(static::class);
        $attributes = $reflector->getAttributes(Entity::class);

        $repositoryClass = $attributes[0]->getArguments()['repositoryClass'] ?? null;
        if ($repositoryClass === null) {
            throw new LogicException('repositoryClass is required');
        }
        return $repositoryClass;
    }

    static protected function columnsMapping(): array
    {
        $reflector = new \ReflectionClass(static::class);
        $columns = [];
        foreach ($reflector->getAttributes(Column::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $columns[] = $attribute->newInstance();
        }
        return $columns;
    }
}
