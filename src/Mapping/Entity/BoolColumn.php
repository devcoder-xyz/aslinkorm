<?php

namespace AlphaSoft\AsLinkOrm\Mapping\Entity;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::IS_REPEATABLE)]
final class BoolColumn extends Column
{
    final public function __construct(
        string $property,
        mixed $defaultValue = null,
        ?string $name = null
    )
    {
        parent::__construct($property, 'bool', $defaultValue, $name);
    }

}
