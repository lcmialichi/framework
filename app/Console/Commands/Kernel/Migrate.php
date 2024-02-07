<?php

namespace App\Console\Commands\Kernel;

use Database\Migration;
use App\Console\Command;
use QueryBuilder\QueryBuilder;
use App\Configuration\Environment;
use QueryBuilder\Connection\Connection;
use App\ORM\Connection\ConnectionHandler;

class Migrate extends Command
{
    public $command = "migration:up";
    public $description = "migrate database";
    public $options = [
        "--seed" => "seed database after migrate execution if binded",
        "--force" => "force migration execution",
        "--version" => "migrate to a specific version"
    ];
    private ?string $dataBase;

    public function handler()
    {
        $this->setEnvDataBase();
        if (!$this->dataBaseAlreadyCreated()) {
            $this->requestDataBaseCreation();
        }
        $this->createMigrationTableIfNotExists();

        $this->run($this->getMigrations());
        $this->success(" -- Migrations applied with success");
    }

    private function run(array $migrations): void
    {
        asort($migrations);
        $count = count($migrations);
        foreach ($migrations as $migration) {
            $executed = $this->execute(
                $this->withNamespace($migration)
            );

            if ($executed) {
                $count--;
            }
        }

        if ($count == count($migrations)) {
            $this->quote("Nothing to migrate!");
        }
    }

    private function execute(string $namespace): bool
    {
        if (!class_exists($namespace)) {
            throw new \Exception(sprintf("Migration %s not found", $namespace));
        }

        if (!method_exists($namespace, "up")) {
            throw new \Exception(sprintf("Method 'up' not found in %s", $namespace));
        }

        if ($this->migrationAlreadyApplied($namespace) && !$this->force()) {
            return false;
        }

        $instance = $this->container()->make($namespace);
        $instance->up();
        $this->setApplied($namespace);
        if ($this->seed()) {
            $this->executeSeeds($instance);
        }

        return true;
    }

    private function executeSeeds(Migration $migration): void
    {
        $pb = $this->progressBar();
        $seeds = $migration->bindSeeds();
        if (empty($seeds)) {
            return;
        }

        $this->quote(" -- Seeding database...");
        $pb->start(count($seeds));

        foreach ($seeds as $seed) {
            if (!is_string($seed)) {
                throw new \Exception("Seed must be a string");
            }

            if (!class_exists($seed)) {
                throw new \Exception(sprintf("Seed %s not found", $seed));
            }

            $this->container()->make($seed)->handle();
            $pb->increment();
        }

        $pb->finish();
    }

    private function getMigrationsFiles(): array
    {
        return glob(config("migration.path") . "/*.php", );
    }

    private function getMigrations(): array
    {
        if ($this->version() !== null) {
            return [$this->version()];
        }

        return array_map(
            fn($file) => pathinfo(basename($file))['filename'],
            $this->getMigrationsFiles()
        );
    }

    private function withNamespace(string $class)
    {
        return $this->getMigrationNamespace() . "\\" . $class;
    }

    private function dataBaseAlreadyCreated(): bool
    {
        return $this->queryBuilder()->raw("
            SELECT SCHEMA_NAME
            FROM information_schema.SCHEMATA
            WHERE SCHEMA_NAME = :db;"
        )->addParam(":db", $this->dataBase())->execute()->count() > 0;
    }

    private function getMigrationNamespace(): string
    {
        return config("migration.namespace");
    }

    private function queryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->getConnection());
    }

    private function getConnection(): Connection
    {
        return $this->getConnectionHandler()->getConnectionNoDatabase();
    }

    private function getConnectionHandler(): ConnectionHandler
    {
        return resolve(ConnectionHandler::class);
    }

    private function setEnvDataBase(): void
    {
        $this->dataBase = Environment::make()->get("DB_DATABASE");
    }

    private function dataBase(): ?string
    {
        return $this->dataBase;
    }

    private function requestDataBaseCreation(): void
    {
        $this->quote(sprintf("Database %s not found, do you want to create? (y/n)", $this->dataBase()));
        $answer = $this->waitForInteraction(100);

        if (in_array($answer, ["y", "Y"])) {
            $this->queryBuilder()->raw("CREATE DATABASE IF NOT EXISTS {$this->dataBase()};")->execute();
            $this->success("Database created with success");
            return;
        }

        throw new \Exception("Database not created");
    }

    private function createDataBase(): void
    {
        $this->queryBuilder()->raw("CREATE DATABASE IF NOT EXISTS {$this->dataBase()};")->execute();
    }

    private function migrationAlreadyApplied(string $version): bool
    {
        return $this->useDataBase()
            ->select()
            ->from("migration")
            ->where("version", "=", ":version")
            ->addParam(":version", $version)
            ->execute()->count() > 0;
    }

    private function useDataBase(): QueryBuilder
    {
        $query = $this->queryBuilder();
        $query->raw(sprintf("USE %s;", $this->dataBase()))->execute();
        return $query;
    }

    private function setApplied(string $migration): void
    {
        $this->useDataBase()->insert([
            "version" => ":version",
            "apply_time" => ":time"
        ])->into("migration")
            ->addParam(":version", $migration)
            ->addParam(":time", time())
            ->execute();

        $this->output(sprintf("Migration '%s' applied", $migration), 'brown');
    }


    public function createMigrationTableIfNotExists(): void
    {
        $this->useDataBase()->raw("
            CREATE TABLE IF NOT EXISTS `migration` (
                `version` varchar(255) NOT NULL,
                `apply_time` int(11) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`version`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        )->execute();
    }

    private function seed(): bool
    {
        return $this->option("seed") === true;
    }

    private function force(): bool
    {
        return $this->option("force") === true;
    }

    private function version(): ?string
    {
        return $this->option("version");
    }

}