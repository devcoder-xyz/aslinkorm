<?php

namespace AlphaSoft\AsLinkOrm\Serializer;

use AlphaSoft\AsLinkOrm\Entity\AsEntity;

class SerializerToDb
{
    public function serialize(AsEntity $entity): array
    {

        $dbData = [];
        $attributes = $entity->toArray();
        foreach ($entity::getColumns() as $column) {
            $property = $column->getProperty();
            if (!array_key_exists($property, $attributes)) {
                continue;
            }
            $dbData[sprintf('`%s`', $column->getName())] = $attributes[$property];
        }
        return $dbData;
    }
}
