<?php

namespace Uspdev\Replicado\Tests;

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Uspdev\Replicado\DB;
use Dotenv\Dotenv;

class BuildFakerDataTest extends TestCase
{

    public function test_deploy_data()
    {


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
        $this->assertSame(100, (int) $computed['computed']);

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
