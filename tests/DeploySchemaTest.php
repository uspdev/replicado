<?php

namespace Uspdev\Replicado\Tests;

use PHPUnit\Framework\TestCase;
use Uspdev\Replicado\DB;
use Faker\Factory;

class DeploySchemaTest extends TestCase
{
    protected function setUp(): void{
        # ler de um arquivo .env que não deve ser versionado
        putenv('REPLICADO_HOST=');
        putenv('REPLICADO_PORT=5050');
        putenv('REPLICADO_DATABASE=faker');
        putenv('REPLICADO_USERNAME=');
        putenv('REPLICADO_PASSWORD=');
        putenv('REPLICADO_CODUNDCLG=');
    }

    private function getTables(){
        $tables = DB::fetchAll("select name from sysobjects where type = 'U' or type = 'P'");
        return array_column($tables, 'name');
    }

    public function test_deploy_schema(){

        # 1. Drop all tables and do the assert
        foreach($this->getTables() as $table){
            DB::getInstance()->exec("DROP TABLE {$table}");
        }
        $this->assertEmpty($this->getTables());

        # 2. Load PESSOA schema
        $pessoa = file_get_contents(__DIR__. '/' . 'schemas/PESSOA.sql');
        DB::getInstance()->exec($pessoa);
        $this->assertContains('PESSOA', $this->getTables());
    }

    public function test_deploy_data(){
        $faker = Factory::create();
        $sql = "INSERT INTO PESSOA (codpes, nompes, nompesttd) VALUES (convert(int,:codpes),:nompes,:nompesttd)";

        # 1. Populate PESSOA table with 1 control person
        $data = [
            'codpes'    => 123456,
            'nompes'    => 'Fulano da Silva',
            'nompesttd' => 'Fulano da Silva'
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        # 2. Populate PESSOA table with 100 people
        $faker = Factory::create();
        $sql = "INSERT INTO PESSOA (codpes, nompes, nompesttd) VALUES (convert(int,:codpes),:nompes, :nompesttd)";
        for ($i = 0; $i < 100; $i++) {
            $data = [
                'codpes'    => $faker->randomNumber,
                'nompes'    => $faker->name,
                'nompesttd' => $faker->name
            ];
            DB::getInstance()->prepare($sql)->execute($data);
        }

        # Assertion
        $computed = DB::fetch('SELECT COUNT(*) FROM PESSOA');
        $this->assertSame(101, (int) $computed['computed']);
    }
}