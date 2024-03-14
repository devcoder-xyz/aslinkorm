<?php

namespace AlphaSoft\AsLinkOrm\Serializer;

use AlphaSoft\AsLinkOrm\Entity\AsEntity;

final readonly class SerializerToDb
{

    public function __construct(private AsEntity $entity)
    {
    }

    public function serialize(): array
    {
        $entity = $this->entity;
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
