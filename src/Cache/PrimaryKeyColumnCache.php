<?php

namespace AlphaSoft\AsLinkOrm\Cache;

use AlphaSoft\AsLinkOrm\Mapping\Entity\PrimaryKeyColumn;

final class PrimaryKeyColumnCache
{
    private static ?\AlphaSoft\AsLinkOrm\Cache\PrimaryKeyColumnCache $instance = null;
    private array $data = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function set(string $key, PrimaryKeyColumn $primaryKeyColumn): void
    {
        $this->data[$key] = $primaryKeyColumn;
    }

    public function get(string $key): ?PrimaryKeyColumn
    {
        return $this->data[$key] ?? null;
    }
}
