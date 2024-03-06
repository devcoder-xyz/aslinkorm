<?php

namespace AlphaSoft\AsLinkOrm\Mapping\Entity;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class JoinColumn extends Column
{
    final public function __construct(
        string $property,
        private readonly string $referencedColumnName,
        private readonly string $targetEntity,
        mixed $defaultValue = null,
        ?string $name = null
    )
    {
        parent::__construct($property, $defaultValue, $name);
    }

    public function getReferencedColumnName(): string
    {
        return $this->referencedColumnName;
    }

    public function getTargetEntity(): string
    {
        return $this->targetEntity;
    }
}
