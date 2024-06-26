<?php

namespace AlphaSoft\AsLinkOrm\Repository;

use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\EntityManager;
use AlphaSoft\AsLinkOrm\Helper\QueryHelper;
use AlphaSoft\AsLinkOrm\Mapping\Entity\Column;
use AlphaSoft\DataModel\Factory\ModelFactory;
use Doctrine\DBAL\Query\QueryBuilder;
use SplObjectStorage;

abstract class Repository
{
    /**
     * @var array<AsEntity>
     */
    private array $entities = [];

    public function __construct(private readonly EntityManager $manager)
    {
    }

    /**
     * Get the name of the table associated with this repository.
     *
     * @return string The name of the table.
     */
    final public function getTableName(): string
    {
        /**
         * @var class-string<AsEntity> $entityName
         */
        $entityName = $this->getEntityName();
        return $entityName::getTable();
    }

    /**
     * Get the name of the model associated with this repository.
     *
     * @return class-string<AsEntity> The name of the model.
     */
    abstract public function getEntityName(): string;

    public function findOneBy(array $arguments = [], array $orderBy = []): ?object
    {
        $query = $this->generateSelectQuery($arguments, $orderBy);
        $item = $query->fetchAssociative();
        if ($item === false) {
            return null;
        }
        return $this->createModel($item);
    }

    public function findBy(array $arguments = [], array $orderBy = [], ?int $limit = null): SplObjectStorage
    {
        $query = $this->generateSelectQuery($arguments, $orderBy, $limit);
        $data = $query->fetchAllAssociative();

        return $this->createCollection($data);
    }

    public function save(AsEntity $entity): int
    {
        if ($entity->getPrimaryKeyValue()) {
            return $this->update($entity);
        }
        return $this->insert($entity);
    }

    public function insert(AsEntity $entity): int
    {
        $connection = $this->manager->getConnection();
        $query = $connection->createQueryBuilder();
        $query->insert($this->getTableName());

        $primaryKeyColumn = $entity::getPrimaryKeyColumn();
        foreach ($entity->toDb() as $property => $value) {
            if (str_replace('`', '', $property) === $primaryKeyColumn) {
                continue;
            }
            $query->setValue($property, $query->createPositionalParameter($value, QueryHelper::typeOfValue($value)));
        }
        $rows = $query->executeStatement();
        $lastId = $connection->lastInsertId();
        if ($lastId !== false) {
            $entity->set($primaryKeyColumn, $lastId);
            $this->entities[$entity->getPrimaryKeyValue()] = $entity;
            $entity->setEntityManager($this->manager);
        }
        return $rows;
    }

    public function update(AsEntity $entity, array $arguments = []): int
    {
        $query = $this->createQueryBuilder();
        $query->update($this->getTableName());

        $properties = $entity->toDbForUpdate();
        if ($properties === []) {
            return 0;
        }
        $primaryKeyColumn = $entity::getPrimaryKeyColumn();
        foreach ($properties as $property => $value) {
            if (str_replace('`', '', $property) === $primaryKeyColumn) {
                continue;
            }
            $query->set($property, $query->createPositionalParameter($value, QueryHelper::typeOfValue($value)));
        }
        QueryHelper::generateWhereQuery($query, array_merge([$primaryKeyColumn => $entity->getPrimaryKeyValue()], $this->mapPropertiesToColumn($arguments)));
        return $query->executeStatement();
    }

    public function delete(AsEntity $entity): int
    {
        $connection = $this->manager->getConnection();
        $query = $connection->createQueryBuilder();
        $query->delete($this->getTableName())
            ->where($entity::getPrimaryKeyColumn() . ' = ' . $query->createPositionalParameter($entity->getPrimaryKeyValue()));

        $entity->set($entity::getPrimaryKeyColumn(), null);
        $entity->setEntityManager(null);

        return $query->executeStatement();
    }

    /**
     * @param array $arguments
     * @param array<string,string> $orderBy
     * @param int|null $limit
     * @return QueryBuilder
     */
    protected function generateSelectQuery(array $arguments = [], array $orderBy = [], ?int $limit = null): QueryBuilder
    {
        /**
         * @var class-string<AsEntity> $entityName
         */
        $entityName = $this->getEntityName();

        $arguments = $this->mapPropertiesToColumn($arguments);
        $orderBy = $this->mapPropertiesToColumn($orderBy);
        $properties = array_map(fn(Column $column): string => sprintf('`%s`', $column->getName()), $entityName::getColumns());

        $query = $this->createQueryBuilder();
        $query
            ->select(...$properties)
            ->from($this->getTableName());
        QueryHelper::generateWhereQuery($query, $arguments);
        foreach ($orderBy as $property => $order) {
            $query->orderBy($property, $order);
        }
        $query->setMaxResults($limit);
        return $query;
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->manager->getConnection()->createQueryBuilder();
    }

    public function queryUpdate(string $alias = null): QueryBuilder
    {
        return $this->createQueryBuilder()->update($this->getTableName(), $alias);
    }

    public function querySelect(string $alias = null): QueryBuilder
    {
        /**
         * @var class-string<AsEntity> $entityName
         */
        $entityName = $this->getEntityName();
        $properties = array_map(function (Column $column) use ($alias): string {
            if ($alias) {
                return sprintf('`%s`.`%s`', $alias, $column->getName());
            }
            return sprintf('`%s`', $column->getName());
        },
            $entityName::getColumns()
        );
        return $this->createQueryBuilder()
            ->select(...$properties)
            ->from($this->getTableName(), $alias);
    }

    final protected function mapPropertiesToColumn(array $arguments): array
    {
        /**
         * @var class-string<AsEntity> $entityName
         */
        $entityName = $this->getEntityName();
        $dbArguments = [];

        foreach ($arguments as $property => $value) {
            $column = $entityName::mapPropertyToColumn($property);
            $dbArguments[$column] = $value;
        }

        return $dbArguments;
    }

    final protected function createModel(array $data): object
    {
        /**
         * @var class-string<AsEntity> $entityName
         */
        $entityName = $this->getEntityName();
        $primaryKeyValue = $data[$entityName::getPrimaryKeyColumn()];
        if (array_key_exists($primaryKeyValue, $this->entities)) {
            $entity = $this->entities[$primaryKeyValue];
            $entity->hydrate($data);
        } else {
            $entity = ModelFactory::createModel($this->getEntityName(), $data);
            $this->entities[$primaryKeyValue] = $entity;
        }

        if (is_subclass_of($entity, AsEntity::class)) {
            $entity->setEntityManager($this->manager);
        }
        return $entity;
    }

    final protected function createCollection(array $dataCollection): SplObjectStorage
    {
        $storage = new SplObjectStorage();
        foreach ($dataCollection as $data) {
            $entity = $this->createModel($data);
            $storage->attach($entity);
        }
        return $storage;
    }

    public function clear(): void
    {
        foreach ($this->entities as $objet) {
            $objet->setEntityManager(null);
            $objet->set($objet::getPrimaryKeyColumn(), null);
        }
        $this->entities = [];
    }
}
