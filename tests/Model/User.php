<?php

namespace Test\AlphaSoft\AsLinkOrm\Model;

use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\Mapping\Entity\BoolColumn;
use AlphaSoft\AsLinkOrm\Mapping\Entity\Column;
use AlphaSoft\AsLinkOrm\Mapping\Entity\Entity;
use AlphaSoft\AsLinkOrm\Mapping\Entity\OneToMany;
use AlphaSoft\AsLinkOrm\Mapping\Entity\PrimaryKeyColumn;
use Test\AlphaSoft\AsLinkOrm\Repository\PostRepository;
use Test\AlphaSoft\AsLinkOrm\Repository\UserRepository;

#[Entity(table: "user", repositoryClass: UserRepository::class)]
#[PrimaryKeyColumn(property: 'id', type: 'int')]
#[Column(property: 'firstname')]
#[Column(property: 'lastname')]
#[Column(property: 'email')]
#[Column(property: 'password')]
#[BoolColumn(property: 'isActive', defaultValue: false, name: 'is_active')]
#[OneToMany(targetEntity: Post::class, criteria: ['user_id' => 'id'])]
final class User extends AsEntity
{
    public function getPrimaryKeyValue(): ?int
    {
        return $this->get(self::getPrimaryKeyColumn());
    }
    public function getFirstname(): ?string
    {
        return $this->get('firstname');
    }
    public function setFirstname(?string $firstname): self
    {
        $this->set('firstname', $firstname);
        return $this;
    }
    public function getLastname(): ?string
    {
        return $this->get('lastname');
    }
    public function setLastname(?string $lastname): self
    {
        $this->set('lastname', $lastname);
        return $this;
    }
    public function getEmail(): ?string
    {
        return $this->get('email');
    }
    public function setEmail(?string $email): self
    {
        $this->set('email', $email);
        return $this;
    }
    public function getPassword(): ?string
    {
        return $this->get('password');
    }
    public function setPassword(?string $password): self
    {
        $this->set('password', $password);
        return $this;
    }
    public function getIsActive(): ?bool
    {
        return $this->get('isActive');
    }
    public function setIsActive(?bool $isActive): self
    {
        $this->set('isActive', $isActive);
        return $this;
    }

    public function getPosts(): \SplObjectStorage
    {
        return $this->hasMany(\Test\AlphaSoft\AsLinkOrm\Model\Post::class, ['user_id' => $this->getPrimaryKeyValue()]);
    }
}
