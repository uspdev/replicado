<?php

namespace Uspdev\Replicado\Tests;

use PHPUnit\Framework\TestCase;
use Uspdev\Replicado\Pessoa;
use Uspdev\Replicado\DB;
use Faker\Factory;

class PessoaTest extends TestCase
{
    public function test_nomeCompleto(){
        # Limpando Tabela
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();

        $sql = "INSERT INTO PESSOA (codpes, nompes, nompesttd) VALUES 
                                   (convert(int,:codpes),:nompes,:nompesttd)";

        $data = [
            'codpes' => 123456,
            'nompes' => 'Fulano da Silva',
            'nompesttd' => 'Fulana da Silva',
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('Fulana da Silva',Pessoa::nomeCompleto(123456));
    }
}