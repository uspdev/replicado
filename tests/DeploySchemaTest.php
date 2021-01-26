<?php

namespace Uspdev\Replicado\Tests;

use PHPUnit\Framework\TestCase;
use Uspdev\Replicado\DB;
use Dotenv\Dotenv;

class DeploySchemaTest extends TestCase
{
    protected function setUp(): void
    {
        $dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/../');
        $dotenv->load();

        putenv('REPLICADO_HOST=' . getenv('TEST_SYBASE_HOST'));
        putenv('REPLICADO_PORT=' . getenv('TEST_SYBASE_PORT'));
        putenv('REPLICADO_DATABASE=' . getenv('TEST_SYBASE_DATABASE'));
        putenv('REPLICADO_USERNAME=' . getenv('TEST_SYBASE_USERNAME'));
        putenv('REPLICADO_PASSWORD=' . getenv('TEST_SYBASE_PASSWORD'));
        putenv('REPLICADO_CODUNDCLG=' . getenv('TEST_SYBASE_CODUNDCLG'));
        putenv('REPLICADO_SYBASE=1');
    }

    public function test_deploy_schema()
    {
        # Drop all tables and do the assert
        foreach ($this->getTables() as $table) {
            DB::getInstance()->exec("DROP TABLE {$table}");
        }
        $this->assertEmpty($this->getTables());

        # Deploy all sql's in schemas folder
        $schemas = scandir(__DIR__ . '/schemas/');
        $schemas = array_diff( $schemas, ['.','..'] );
        foreach($schemas as $schema){
            if ($schema != 'database.sql') {
                $sql = file_get_contents(__DIR__ . '/schemas/' . $schema);
                DB::getInstance()->exec($sql);
                $table = explode('.',$schema)[0];
                $this->assertContains($table, $this->getTables());
            }
        }
    }

    /* Métodos Auxiliares */
    private function getTables()
    {
        $tables = DB::fetchAll("select name from sysobjects where type = 'U' or type = 'P'");
        return array_column($tables, 'name');
    }
}
