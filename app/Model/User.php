<?php

namespace App\Model;

use App\ORM\Connection\Group\ConnectionGroup;
use App\ORM\Model;
use App\ORM\Attributes\Table;
use App\ORM\Connection\Group\TestConnection;

#[Table('user')]
class User extends Model
{
    public function connectionGroup(): ConnectionGroup
    {
        return resolve(TestConnection::class);
    }

}
