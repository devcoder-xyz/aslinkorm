<?php

namespace Test\AlphaSoft\AsLinkOrm\Model;

use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\Mapping\Entity\Column;
use AlphaSoft\AsLinkOrm\Mapping\Entity\Entity;
use AlphaSoft\AsLinkOrm\Mapping\Entity\JoinColumn;
use AlphaSoft\AsLinkOrm\Mapping\Entity\PrimaryKeyColumn;
use Test\AlphaSoft\AsLinkOrm\Repository\PostRepository;

#[Entity(table: "post", repositoryClass: PostRepository::class)]
#[PrimaryKeyColumn(property: 'id', type: 'int')]
#[Column(property: 'title')]
#[Column(property: 'content')]
#[JoinColumn(property: 'user_id', referencedColumnName: 'id', targetEntity: User::class)]
final class Post extends AsEntity
{
    public function getTitle(): ?string
    {
        return $this->get('title');
    }
    public function setTitle(?string $title): self
    {
        $this->set('title', $title);
        return $this;
    }
    public function getContent(): ?string
    {
        return $this->get('content');
    }
    public function setContent(?string $content): self
    {
        $this->set('content', $content);
        return $this;
    }
    public function getUser(): ?\Test\AlphaSoft\AsLinkOrm\Model\User
    {
        return $this->hasOne(\Test\AlphaSoft\AsLinkOrm\Model\User::class, ['id' => $this->get('user_id')]);
    }
    public function setUser(?\Test\AlphaSoft\AsLinkOrm\Model\User $user): self
    {
        $this->set('user_id', $user->getPrimaryKeyValue());
        return $this;
    }
}
