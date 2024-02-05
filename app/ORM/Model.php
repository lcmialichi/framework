<?php

namespace App\ORM;

use App\ORM\Connection\Group\ConnectionGroup;

class Model extends Orchestrator
{
    public function connectionGroup(): ConnectionGroup
    {
        return resolve(ConnectionGroup::class);
    }
}
