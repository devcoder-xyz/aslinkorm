<?php

namespace AlphaSoft\AsLinkOrm;

use AlphaSoft\AsLinkOrm\Debugger\SqlDebugger;
use AlphaSoft\AsLinkOrm\Driver\DriverInterface;
use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\Platform\PlatformInterface;
use AlphaSoft\AsLinkOrm\Platform\AssqlPlatform;
use AlphaSoft\AsLinkOrm\Repository\Repository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use InvalidArgumentException;

class EntityManager
{
    private AsLinkConnection $connection;

    /**
     * @var array<Repository>
     */
    private array $repositories = [];

    /**
     * @var array<AsEntity>
     */
    private array $trackedEntities = [];

    public function __construct(array $params)
    {
        $params['wrapperClass'] = AsLinkConnection::class;
        $this->connection = DriverManager::getConnection($params);
        $this->connection->setSqlDebugger(new SqlDebugger());
    }

    public function getConnection(): AsLinkConnection
    {
        return $this->connection;
    }

    public function getRepository(string $repository): Repository
    {
        if (is_subclass_of($repository, AsEntity::class)) {
            $repository = $repository::getRepositoryName();
        }

        if (!is_subclass_of($repository, Repository::class)) {
            throw new InvalidArgumentException($repository . ' must be an instance of ' . Repository::class);
        }

        if (!isset($this->repositories[$repository])) {
            $this->repositories[$repository] = new $repository($this);
        }
        return $this->repositories[$repository];
    }

    public function persist(AsEntity $entity): void
    {
        $this->trackedEntities[] = $entity;
    }

    public function flush(): void
    {
        foreach ($this->trackedEntities as $entity) {
            $repository = $this->getRepository(get_class($entity));
            $repository->save($entity);
        }
        $this->trackedEntities = [];
    }

    public function createDatabasePlatform(): PlatformInterface
    {
        $driver = $this->connection->getDriver();
        if ($driver instanceof DriverInterface) {
            return $driver->createDatabasePlatform($this->getConnection());
        }
        throw new InvalidArgumentException(get_class($driver) . ' must implement the ' . DriverInterface::class . ' interface in order to create the database platform.');
    }

    public function clearAll(): void
    {
        foreach ($this->repositories as $repository) {
            $repository->clear();
        }
    }
}
