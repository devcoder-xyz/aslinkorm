<?php

namespace AlphaSoft\AsLinkOrm\Platform;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class AssqlPlatform implements PlatformInterface
{
    private ?Connection $serverConnection = null;
    private array $params;
    public function __construct(private readonly Connection $connection)
    {
        $this->params = $connection->getParams();
    }

    public function getDatabaseName(): string
    {
        return $this->params['dbname'];
    }

    public function listTables(): array
    {
        $query = $this->connection->executeQuery('SHOW TABLES');
        return $query->fetchAllAssociative();
    }

    public function listDatabases(): array
    {
        $query = $this->getServerConnection()->executeQuery('SHOW DATABASES');
        $data = $query->fetchAllAssociative();
        $this->getServerConnection()->close();
        return $data;
    }
    public function createDatabase(): void
    {
        $this->getServerConnection()->executeQuery(sprintf('CREATE DATABASE "%s"', $this->getDatabaseName()));
        $this->getServerConnection()->close();
    }
    public function createDatabaseIfNotExists(): void
    {
        $this->getServerConnection()->executeQuery(sprintf('CREATE DATABASE IF NOT EXISTS "%s"', $this->getDatabaseName()));
        $this->getServerConnection()->close();
    }

    public function dropDatabase(): void
    {
        $this->getServerConnection()->executeQuery(sprintf('DROP DATABASE "%s"', $this->getDatabaseName()));
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
