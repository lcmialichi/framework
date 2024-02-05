<?php

namespace App\Routes\Collections;

use App\Routes\Group;

class GroupCollection
{
    public static array $groups = [];

    public static function addGroup(Group $group): void
    {   
        self::$groups[$group->getName()] = $group;
    }

    public static function getGroups(): array
    {
        return self::$groups;
    }

}
