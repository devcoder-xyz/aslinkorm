<?php

namespace AlphaSoft\AsLinkOrm\Platform;

use AlphaSoft\AsLinkOrm\AsLinkConnection;
use AlphaSoft\AsLinkOrm\Schema\SchemaInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class AssqlPlatform implements PlatformInterface
{
    private ?Connection $serverConnection = null;
    private array $params;
    public function __construct(private readonly AsLinkConnection $connection, private readonly SchemaInterface $schema)
    {
        $this->params = $connection->getParams();
    }

    public function getDatabaseName(): string
    {
        return $this->params['dbname'];
    }

    public function listTables(): array
    {
        $query = $this->connection->executeQuery($this->schema->showTables());
        return $query->fetchAllAssociative();
    }

    public function listDatabases(): array
    {
        $query = $this->getServerConnection()->executeQuery($this->schema->showDatabases());
        $data = $query->fetchAllAssociative();
        $this->getServerConnection()->close();
        return $data;
    }
    public function createDatabase(): void
    {
        $this->getServerConnection()->executeQuery($this->schema->createDatabase($this->getDatabaseName()));
        $this->getServerConnection()->close();
    }
    public function createDatabaseIfNotExists(): void
    {
        $this->getServerConnection()->executeQuery($this->schema->createDatabaseIfNotExists($this->getDatabaseName()));
        $this->getServerConnection()->close();
    }

    public function dropDatabase(): void
    {
        $this->getServerConnection()->executeQuery($this->schema->dropDatabase($this->getDatabaseName()));
        $this->getServerConnection()->close();
    }

    private function getServerConnection(): Connection
    {
        if ($this->serverConnection === null) {
            $params = $this->params;
            $params['dbname'] = 'null';
            $this->serverConnection = DriverManager::getConnection($params);
        }

        if (!$this->serverConnection->isConnected()) {
            $this->serverConnection->connect();
        }
        return $this->serverConnection;
    }
}
