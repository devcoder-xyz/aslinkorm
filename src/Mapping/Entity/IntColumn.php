<?php

namespace AlphaSoft\AsLinkOrm\Mapping\Entity;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::IS_REPEATABLE)]
final class IntColumn extends Column
{
    final public function __construct(
        string $property,
        mixed $defaultValue = null,
        ?string $name = null
    )
    {
        parent::__construct($property, 'int', $defaultValue, $name);
    }

}
