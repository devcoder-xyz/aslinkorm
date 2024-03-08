<?php

namespace AlphaSoft\AsLinkOrm\Mapper;

use AlphaSoft\AsLinkOrm\Cache\OneToManyCache;
use AlphaSoft\AsLinkOrm\Mapping\Entity\OneToMany;

final class OneToManyRelationMapper
{
    /**
     * @return array<OneToMany>
     */
     static public function getOneToManyRelations(string $class): array
    {
        $cache = OneToManyCache::getInstance();
        if (empty($cache->get($class))) {
            $cache->set($class, self::oneToManyRelationsMapping($class));
        }
        return $cache->get($class);
    }

    static private function oneToManyRelationsMapping(string $class): array
    {
        $reflector = new \ReflectionClass($class);
        $relations = [];
        foreach ($reflector->getAttributes(OneToMany::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $relations[] = $attribute->newInstance();
        }
        return $relations;
    }
}
