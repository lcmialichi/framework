<?php

namespace Database\Migrations;

use Database\Seeds\UserSeed;
use QueryBuilder\Macro\Schemma\Columns;

class AutoGenerated_1704888438 extends \Database\Migration
{
    public function up(): void
    {
        $this->create()->table("users")->columns(function (Columns $col) {
            $col->add("id")->int(11)->notNull()->autoIncrement()->primaryKey();
            $col->add("login")->varchar(45)->default("NULL");
            $col->add("password")->varchar(45)->default("NULL");
            $col->add("status")->bool()->default("true");
        })->execute();
    }

    public function down(): void
    {
        //
    }

    public function bindSeeds(): array
    {
        return [
            UserSeed::class,
        ];
    }
}