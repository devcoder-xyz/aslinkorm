<?php

namespace AlphaSoft\AsLinkOrm\Serializer;

use AlphaSoft\AsLinkOrm\Entity\AsEntity;

final readonly class SerializerToDbForUpdate
{
    public function __construct(private AsEntity $entity)
    {
    }

    public function serialize(): array
    {
        $entity = $this->entity;
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
