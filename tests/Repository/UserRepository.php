<?php

namespace Test\AlphaSoft\AsLinkOrm\Repository;

use AlphaSoft\AsLinkOrm\Repository\Repository;
use Test\AlphaSoft\AsLinkOrm\Model\User;

class UserRepository extends Repository
{
    public function getEntityName(): string
    {
        return User::class;
    }
}
