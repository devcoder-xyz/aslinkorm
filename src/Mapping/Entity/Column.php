<?php

namespace AlphaSoft\AsLinkOrm\Mapping\Entity;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::IS_REPEATABLE)]
class Column implements \Stringable
{
    public function __construct(
        private readonly string $property,
        private readonly string $type = 'string',
        private readonly mixed $defaultValue = null,
        private readonly ?string $name = null
    )
    {
    }

    final public function __toString(): string
    {
        return $this->getProperty();
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    final public function getName(): ?string
    {
        return $this->name ?: $this->getProperty();
    }

    /**
     * @return mixed|null
     */
    final public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
