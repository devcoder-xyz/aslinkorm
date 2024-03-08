<?php

namespace AlphaSoft\AsLinkOrm\Mapping\Entity;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class OneToMany
{
    private \SplObjectStorage $storage;
    final public function __construct(
        private readonly string $targetEntity,
        private readonly array $criteria = [],
    )
    {
        $this->storage = new \SplObjectStorage();
    }

    public function getTargetEntity(): string
    {
        return $this->targetEntity;
    }

    public function getCriteria(): array
    {
        return $this->criteria;
    }

    public function getStorage():\SplObjectStorage
    {
        return $this->storage;
    }
    public function getShortName(): string
    {
        return (new \ReflectionClass($this->getTargetEntity()))->getShortName();
    }
    public function getType(): string
    {
        return '\\'.ltrim(get_class($this->getStorage()), '\\');
    }
}
