<?php

namespace AlphaSoft\AsLinkOrm;

use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\Platform\PlatformInterface;
use AlphaSoft\AsLinkOrm\Platform\SqlPlatform;
use AlphaSoft\AsLinkOrm\Repository\Repository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class EntityManager
{
    private \Doctrine\DBAL\Connection $connection;

    /**
     * @var array<Repository>
     */
    private array $repositories = [];

    public function __construct(array $params, private $platformClassName = null)
    {
        $this->connection = DriverManager::getConnection($params);
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function getRepository(string $repository): Repository
    {
        if (is_subclass_of($repository, AsEntity::class)) {
            $repository = $repository::getRepositoryName();
        }

        if (!is_subclass_of($repository, Repository::class))  {
            throw new \InvalidArgumentException($repository. ' must be an instance of '.Repository::class);
        }

        if (!isset($this->repositories[$repository])) {
            $this->repositories[$repository] = new $repository($this);
        }
        return  $this->repositories[$repository];
    }

    public function createDatabasePlatform(): PlatformInterface
    {
        $platformClassName = $this->platformClassName ?: SqlPlatform::class;
        return new $platformClassName($this->getConnection());
    }

    public function clearAll(): void
    {
        foreach ($this->repositories as $repository) {
            $repository->clear();
        }
    }
}
