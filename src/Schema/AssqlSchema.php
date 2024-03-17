<?php

namespace AlphaSoft\AsLinkOrm\Schema;

class AssqlSchema implements SchemaInterface
{

    public function showDatabases(): string
    {
        return 'SHOW DATABASES';
    }

    public function showTables(): string
    {
        return 'SHOW TABLES';
    }

    public function createDatabase(string $databaseName): string
    {
        return sprintf('CREATE DATABASE "%s"', $databaseName);
    }

    public function createDatabaseIfNotExists(string $databaseName): string
    {
        return sprintf('CREATE DATABASE IF NOT EXISTS "%s"', $databaseName);
    }

    public function dropDatabase(string $databaseName): string
    {
        return sprintf('DROP DATABASE "%s"', $databaseName);
    }

    public function createTable(string $tableName, array $columns, array $options = []): string
    {
        // TODO: Implement createTable() method.
    }

    public function dropTable(string $tableName): string
    {
        // TODO: Implement dropTable() method.
    }

    public function renameTable(string $oldTableName, string $newTableName): string
    {
        // TODO: Implement renameTable() method.
    }

    public function truncateTable(string $tableName): string
    {
        // TODO: Implement truncateTable() method.
    }

    public function alterTable(string $tableName, array $changes): string
    {
        // TODO: Implement alterTable() method.
    }

    public function addColumn(string $tableName, string $columnName, string $columnType, array $options = []): string
    {
        // TODO: Implement addColumn() method.
    }

    public function dropColumn(string $tableName, string $columnName): string
    {
        // TODO: Implement dropColumn() method.
    }

    public function renameColumn(string $tableName, string $oldColumnName, string $newColumnName): string
    {
        // TODO: Implement renameColumn() method.
    }

    public function modifyColumn(string $tableName, string $columnName, string $newColumnType, array $options = []): string
    {
        // TODO: Implement modifyColumn() method.
    }

    public function createIndex(string $indexName, string $tableName, array $columns, array $options = []): string
    {
        // TODO: Implement createIndex() method.
    }

    public function dropIndex(string $indexName, string $tableName): string
    {
        // TODO: Implement dropIndex() method.
    }
}