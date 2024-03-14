<?php

namespace AlphaSoft\AsLinkOrm\Driver;

use AlphaSoft\AsLinkOrm\Platform\PlatformInterface;
use Doctrine\DBAL\Driver\PDO\Connection;

interface DriverInterface
{
    public function connect(array $params): Connection;
    public function createDatabasePlatform(\Doctrine\DBAL\Connection $connection): PlatformInterface;
}