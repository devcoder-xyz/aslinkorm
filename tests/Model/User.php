<?php

namespace Test\AlphaSoft\AsLinkOrm\Model;

use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\Mapping\Entity\Column;
use AlphaSoft\AsLinkOrm\Mapping\Entity\Entity;
use AlphaSoft\AsLinkOrm\Mapping\Entity\PrimaryKeyColumn;
use Test\AlphaSoft\AsLinkOrm\Repository\UserRepository;

#[Entity(table: "user", repositoryClass: UserRepository::class)]
final class User extends AsEntity
{
    public function getPrimaryKeyValue(): ?int
    {
        return $this->get(self::getPrimaryKeyColumn());
    }

    public function getPosts(): \SplObjectStorage
    {
        return $this->hasMany(Post::class, ['user_id' => $this->getPrimaryKeyValue()]);
    }

    static protected function columnsMapping(): array
    {
        return [
            new PrimaryKeyColumn('id'),
            new Column('firstname'),
            new Column('lastname'),
            new Column('email'),
            new Column('password'),
            new Column('isActive', false , 'is_active'),
        ];
    }
}
