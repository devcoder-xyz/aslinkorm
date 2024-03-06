<?php

namespace Test\AlphaSoft\AsLinkOrm\Model;

use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\Mapping\Entity\Column;
use AlphaSoft\AsLinkOrm\Mapping\Entity\Entity;
use AlphaSoft\AsLinkOrm\Mapping\Entity\JoinColumn;
use AlphaSoft\AsLinkOrm\Mapping\Entity\PrimaryKeyColumn;
use Test\AlphaSoft\AsLinkOrm\Repository\PostRepository;

#[Entity(table: "post", repositoryClass: PostRepository::class)]
#[PrimaryKeyColumn(property: 'id')]
#[Column(property: 'title')]
#[Column(property: 'content')]
#[JoinColumn(property: 'user_id', referencedColumnName: 'id', targetEntity: User::class)]
final class Post extends AsEntity
{
    public function getPrimaryKeyValue(): ?int
    {
        return $this->get(self::getPrimaryKeyColumn());
    }

    public function setUser(User $user): self
    {
        $this->set('user_id', $user->getPrimaryKeyValue());
        return $this;
    }
    
    public function getId()
    {
       return $this->get('id');
    }

    public function getTitle()
    {
       return $this->get('title');
    }

    public function setTitle($value): self
    {
        $this->set('title', $value);
        return $this;
    }

    public function getContent()
    {
       return $this->get('content');
    }

    public function setContent($value): self
    {
        $this->set('content', $value);
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->hasOne(\Test\AlphaSoft\AsLinkOrm\Model\User::class, ['id' => $this->get('user_id')]);
    }
    public function setUserId($value): self
    {
        $this->set('user_id', $value);
        return $this;
    }
}
