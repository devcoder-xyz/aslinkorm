<?php

namespace AlphaSoft\AsLinkOrm\Cache;

use AlphaSoft\AsLinkOrm\Mapping\Entity\Column;

final class ColumnCache
{
    private static ?\AlphaSoft\AsLinkOrm\Cache\ColumnCache $instance = null;
    private array $data = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function set(string $key, array $columns): void
    {
        foreach ($columns as $column) {
            if (!$column instanceof Column) {
                throw new \InvalidArgumentException('All values in the array must be instances of Column.');
            }
        }

        $this->data[$key] = $columns;
    }

    public function get(string $key): array
    {
        return $this->data[$key] ?? [];
    }
}
