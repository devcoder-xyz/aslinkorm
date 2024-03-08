<?php

namespace AlphaSoft\AsLinkOrm\Serializer;

use AlphaSoft\AsLinkOrm\Entity\AsEntity;

class SerializerToDbForUpdate
{
    public function serialize(AsEntity $entity): array
    {
        $dbData = [];
        $modifiedAttributes = $entity->getModifiedAttributes();
        foreach ($entity::getColumns() as $column) {
            $property = $column->getProperty();
            if (!array_key_exists($property, $modifiedAttributes)) {
                continue;
            }
            $dbData[sprintf('`%s`', $column->getName())] = $modifiedAttributes[$property];
        }
        return $dbData;
    }
}
