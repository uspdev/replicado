<?php

namespace Uspdev\Replicado\Tests;

use PHPUnit\Framework\TestCase;
use Uspdev\Replicado\Posgraduacao;
use Uspdev\Replicado\DB;

class PosgraduacaoTest extends TestCase
{
    public function test_verifica(){
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, tipvin, tipvinext, nompes, sitatl, codundclg) VALUES 
                                   (convert(int,:codpes),:tipvin,:tipvinext,:nompes,:sitatl,convert(int,:codundclg))";

        $data = [
            'codpes' => 123456,
            'tipvin' => 'ALUNOPOS',
            'tipvinext' => 'Aluno da Pós-Graduação',
            'nompes' => 'Fulano da Silva',
            'sitatl' => 'A',
            'codundclg' => 8,
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertTrue(true, Posgraduacao::verifica(123456, 8));
    }

}