<?php

namespace AlphaSoft\AsLinkOrm;

use AlphaSoft\AsLinkOrm\Debugger\SqlDebugger;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;

class AsLinkConnection extends Connection
{
    private ?SqlDebugger $sqlDebugger = null;

    public function setSqlDebugger(SqlDebugger $sqlDebugger): void
    {
        $this->sqlDebugger = $sqlDebugger;
    }

    public function executeStatement($sql, array $params = [], array $types = []): int
    {
        $this->sqlDebugger->startQuery($sql, $params);
        $result = parent::executeStatement($sql,  $params, $types);
        $this->sqlDebugger->stopQuery();
        return $result;
    }

    public function executeQuery(string $sql, array $params = [], $types = [], ?QueryCacheProfile $qcp = null): Result
    {
        $this->sqlDebugger->startQuery($sql, $params);
        $result = parent::executeQuery($sql,  $params , $types, $qcp);
        $this->sqlDebugger->stopQuery();
        return $result;
    }

    public function getSqlDebugger(): ?SqlDebugger
    {
        return $this->sqlDebugger;
    }
}