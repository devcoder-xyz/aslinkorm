<?php

namespace AlphaSoft\AsLinkOrm\Entity;

use AlphaSoft\AsLinkOrm\Coordinator\EntityRelationCoordinator;
use AlphaSoft\AsLinkOrm\EntityManager;
use AlphaSoft\AsLinkOrm\Mapper\ColumnMapper;
use AlphaSoft\AsLinkOrm\Mapper\EntityMapper;
use AlphaSoft\AsLinkOrm\Mapper\OneToManyRelationMapper;
use AlphaSoft\AsLinkOrm\Mapping\Entity\Column;
use AlphaSoft\AsLinkOrm\Mapping\Entity\JoinColumn;
use AlphaSoft\AsLinkOrm\Mapping\Entity\OneToMany;
use AlphaSoft\AsLinkOrm\Serializer\SerializerToDb;
use AlphaSoft\AsLinkOrm\Serializer\SerializerToDbForUpdate;
use AlphaSoft\DataModel\Model;
use SplObjectStorage;

abstract class AsEntity extends Model
{
    private ?EntityRelationCoordinator $__relationCoordinator = null;
    private array $_modifiedAttributes = [];

    final public function set(string $property, mixed $value): static
    {
        $property = static::mapColumnToProperty($property);
        if ($value !== $this->getOrNull($property)) {
            $this->_modifiedAttributes[$property] = $value;
        }
        parent::set($property, $value);

        return $this;
    }

    public function getModifiedAttributes(): array
    {
        return $this->_modifiedAttributes;
    }

    final public function toDb(): array
    {
        return (new SerializerToDb())->serialize($this);
    }

    final public function toDbForUpdate(): array
    {
        return (new SerializerToDbForUpdate())->serialize($this);
    }

    public function setEntityManager(?EntityManager $manager): void
    {
        $this->__relationCoordinator = $manager ? new EntityRelationCoordinator($manager) : null;
    }

    protected function hasOne(string $relatedModel, array $criteria = []): ?object
    {
        $hasColumn = false;
        foreach (static::getColumns() as $column) {
            if (!$column instanceof JoinColumn) {
                continue;
            }

            if ($column->getTargetEntity() == $relatedModel) {
                $hasColumn = true;
                break;
            }
        }

        if ($hasColumn === false) {
            throw new \LogicException("No JoinColumn relation defined for the related model '$relatedModel'.");
        }

        return $this->__relationCoordinator?->hasOne($relatedModel, $criteria) ?: null;
    }

    protected function hasMany(string $relatedModel, array $criteria = []): SplObjectStorage
    {
        $defaultStorage = null;
        foreach (static::getOneToManyRelations() as $oneToManyRelation) {
            if ($oneToManyRelation->getTargetEntity() == $relatedModel) {
                $defaultStorage = $oneToManyRelation->getStorage();
                break;
            }
        }

        if ($defaultStorage === null) {
            throw new \LogicException("No OneToMany relation defined for the related model '$relatedModel'.");
        }

        return $this->__relationCoordinator?->hasMany($relatedModel, $criteria) ?: $defaultStorage;
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
        return ColumnMapper::getPrimaryKeyColumn(static::class);
    }

    /**
     * @return array<Column>
     */
    final static public function getColumns(): array
    {
        return ColumnMapper::getColumns(static::class);
    }

    /**
     * @return array<OneToMany>
     */
    final static public function getOneToManyRelations(): array
    {
        return OneToManyRelationMapper::getOneToManyRelations(static::class);
    }

    static public function getTable(): string
    {
        return EntityMapper::getTable(static::class);
    }

    static public function getRepositoryName(): string
    {
        return EntityMapper::getRepositoryName(static::class);
    }
}
