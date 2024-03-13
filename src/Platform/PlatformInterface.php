<?php

namespace AlphaSoft\AsLinkOrm\Platform;

interface PlatformInterface
{
    public function createDatabase(): void;
    public function createDatabaseIfNotExists(): void;
    public function getDatabaseName(): string;
    public function dropDatabase(): void;

}
