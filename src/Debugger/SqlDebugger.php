<?php

namespace AlphaSoft\AsLinkOrm\Debugger;

final class SqlDebugger
{
    private array $queries = [];

    public int $currentQuery = 0;

    public function startQuery(string $query, array $params): void
    {
        $this->queries[++$this->currentQuery] = [
            'query' => $query,
            'params' => $params,
            'startTime' => microtime(true),
            'executionTime' => 0
        ];
    }

    public function stopQuery(): void
    {
        if (!isset($this->queries[$this->currentQuery]['startTime'])) {
            throw new \LogicException('stopQuery() called without startQuery()');
        }

        $start = $this->queries[$this->currentQuery]['startTime'];
        $this->queries[$this->currentQuery]['executionTime'] = microtime(true) - $start;
    }

    public function getQueries(): array
    {
        return $this->queries;
    }
}