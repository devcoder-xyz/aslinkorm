<?php

namespace AlphaSoft\AsLinkOrm\Mapper;

use AlphaSoft\AsLinkOrm\Cache\ColumnCache;
use AlphaSoft\AsLinkOrm\Cache\PrimaryKeyColumnCache;
use AlphaSoft\AsLinkOrm\Mapping\Entity\Column;
use AlphaSoft\AsLinkOrm\Mapping\Entity\PrimaryKeyColumn;

final class ColumnMapper
{

    static public function getPrimaryKeyColumn(string $class): string
    {
        $cache = PrimaryKeyColumnCache::getInstance();
        if (!$cache->get($class) instanceof \AlphaSoft\AsLinkOrm\Mapping\Entity\PrimaryKeyColumn) {

            $columnsFiltered = array_filter(self::getColumns($class), fn(Column $column): bool => $column instanceof PrimaryKeyColumn);

            if (count($columnsFiltered) === 0) {
                throw new \LogicException('At least one primary key is required.');
            }

            if (count($columnsFiltered) > 1) {
                throw new \LogicException('Only one primary key is allowed.');
            }

            $primaryKey = $columnsFiltered[0];

            $cache->set($class, $primaryKey);
        }
        return $cache->get($class)->getName();
    }

    /**
     * @return array<Column>
     */
     static public function getColumns(string $class): array
    {
        $cache = ColumnCache::getInstance();
        if (empty($cache->get($class))) {
            $cache->set($class, self::columnsMapping($class));
        }
        return $cache->get($class);
    }

    static private function columnsMapping(string $class): array
    {
        $reflector = new \ReflectionClass($class);
        $columns = [];
        foreach ($reflector->getAttributes(Column::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $columns[] = $attribute->newInstance();
        }
        return $columns;
    }
}
