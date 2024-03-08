<?php

namespace AlphaSoft\AsLinkOrm\Cache;

use AlphaSoft\AsLinkOrm\Mapping\Entity\OneToMany;

final class OneToManyCache
{
    private static ?\AlphaSoft\AsLinkOrm\Cache\OneToManyCache $instance = null;
    private array $data = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function set(string $key, array $oneToManyRelations): void
    {
        foreach ($oneToManyRelations as $oneToManyRelation) {
            if (!$oneToManyRelation instanceof OneToMany) {
                throw new \InvalidArgumentException('All values in the array must be instances of OneToMany.');
            }
        }

        $this->data[$key] = $oneToManyRelations;
    }

    public function get(string $key): array
    {
        return $this->data[$key] ?? [];
    }
}
