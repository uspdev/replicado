<?php

namespace Uspdev\Replicado\Tests;

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Uspdev\Replicado\DB;

class DeploySchemaTest extends TestCase
{
    protected function setUp(): void
    {
        $dotenv = \Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/../');
        $dotenv->load();

        putenv('REPLICADO_HOST=' . getenv('TEST_SYBASE_HOST'));
        putenv('REPLICADO_PORT=' . getenv('TEST_SYBASE_PORT'));
        putenv('REPLICADO_DATABASE=' . getenv('TEST_SYBASE_DATABASE'));
        putenv('REPLICADO_USERNAME=' . getenv('TEST_SYBASE_USERNAME'));
        putenv('REPLICADO_PASSWORD=' . getenv('TEST_SYBASE_PASSWORD'));
        putenv('REPLICADO_CODUNDCLG=' . getenv('TEST_SYBASE_CODUNDCLG'));
    }

    private function getTables()
    {
        $tables = DB::fetchAll("select name from sysobjects where type = 'U' or type = 'P'");
        return array_column($tables, 'name');
    }

    public function test_deploy_schema()
    {

        # 1. Drop all tables and do the assert
        foreach ($this->getTables() as $table) {
            DB::getInstance()->exec("DROP TABLE {$table}");
        }
        $this->assertEmpty($this->getTables());

        # 2. Load PESSOA schema
        $pessoa = file_get_contents(__DIR__ . '/' . 'schemas/PESSOA.sql');
        DB::getInstance()->exec($pessoa);
        $this->assertContains('PESSOA', $this->getTables());

        # 3. Load LOCALIZAPESSOA schema
        $pessoa = file_get_contents(__DIR__ . '/' . 'schemas/LOCALIZAPESSOA.sql');
        DB::getInstance()->exec($pessoa);
        $this->assertContains('LOCALIZAPESSOA', $this->getTables());

        # 4. Load EMAILPESSOA schema
        $pessoa = file_get_contents(__DIR__ . '/' . 'schemas/EMAILPESSOA.sql');
        DB::getInstance()->exec($pessoa);
        $this->assertContains('EMAILPESSOA', $this->getTables());
    }

    public function test_deploy_data()
    {
        $faker = Factory::create();
        $sql = "INSERT INTO PESSOA (codpes, nompes, nompesttd) VALUES (convert(int,:codpes),:nompes,:nompesttd)";

        # 1. Populate PESSOA table with 1 control person
        $data = [
            'codpes' => 123456,
            'nompes' => 'Fulano da Silva',
            'nompesttd' => 'Fulano da Silva',
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        # 2. Populate PESSOA table with 100 people
        $faker = Factory::create();
        $sql = "INSERT INTO PESSOA (codpes, nompes, nompesttd)
                VALUES (convert(int,:codpes),:nompes, :nompesttd)";
        for ($i = 0; $i < 100; $i++) {
            $data = [
                'codpes' => $faker->randomNumber,
                'nompes' => $faker->name,
                'nompesttd' => $faker->name,
            ];
            DB::getInstance()->prepare($sql)->execute($data);
        }

        # 3. Assertion
        $computed = DB::fetch('SELECT COUNT(*) FROM PESSOA');
        $this->assertSame(101, (int) $computed['computed']);

        # 4. A tabela LOCALIZAPESSOA será baseada na tabela PESSOA
        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, tipvinext, nompes, sitatl, codundclg)
                VALUES (convert(int,:codpes), :tipvinext, :nompes, :sitatl, convert(int,:codundclg))";
        $pessoas = DB::fetchAll('SELECT * FROM PESSOA');
        foreach ($pessoas as $pessoa) {
            $data = [
                'codpes' => $pessoa['codpes'],
                'tipvinext' => 'Servidor',
                'nompes' => $pessoa['nompes'],
                'sitatl' => 'A',
                'codundclg' => 8,
            ];
            DB::getInstance()->prepare($sql)->execute($data);
        }
        $computed = DB::fetch('SELECT COUNT(*) FROM LOCALIZAPESSOA');
        $this->assertSame(101, (int) $computed['computed']);

        # 5. EMAILPESSOA
        $sql = "INSERT INTO EMAILPESSOA (codpes, codema, stamtr)
                VALUES (convert(int,:codpes), :codema, :stamtr)";
        $pessoas = DB::fetchAll('SELECT * FROM PESSOA');
        foreach ($pessoas as $pessoa) {
            $data = [
                'codpes' => $pessoa['codpes'],
                'codema' => $faker->email,
                'stamtr' => 'S',
            ];
            DB::getInstance()->prepare($sql)->execute($data);
        }
        $computed = DB::fetch('SELECT COUNT(*) FROM EMAILPESSOA');
        $this->assertSame(101, (int) $computed['computed']);

    }
}
