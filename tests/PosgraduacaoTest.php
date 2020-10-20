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

    public function test_ativos(){
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();

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

        $sql = "INSERT INTO PESSOA (codpes, nompes, nompesttd) VALUES 
                                   (convert(int,:codpes),:nompes,:nompesttd)";

        $data = [
            'codpes' => 123456,
            'nompes' => 'Fulano da Silva',
            'nompesttd' => 'Fulana da Silva',
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertIsArray(Posgraduacao::ativos(8));
    }

    public function test_programas(){
        DB::getInstance()->prepare('DELETE FROM CURSO')->execute();
        DB::getInstance()->prepare('DELETE FROM NOMECURSO')->execute();

        $sql = "INSERT INTO CURSO (codcur, codclg, tipcur, dtainiccp) VALUES 
                                   (convert(int,:codcur),convert(int,:codclg),:tipcur,:dtainiccp)";

        $data = [
            'codcur' => 123456,
            'codclg' => 8,
            'tipcur' => 'POS',
            'dtainiccp' => '2020-10-20 00:00:00',
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO NOMECURSO (codcur, nomcur, dtafimcur) VALUES 
                                   (convert(int,:codcur),:nomcur,:dtafimcur)";

        $data = [
            'codcur' => 123456,
            'nomcur' => 'Curso Exemplo',
            'dtafimcur' => null,
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertIsArray(Posgraduacao::programas(8));
    }

    public function test_orientadores(){
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();
        DB::getInstance()->prepare('DELETE FROM R25CRECREDOC')->execute();

        $sql = "INSERT INTO PESSOA (codpes, nompes, nompesttd) VALUES 
                                (convert(int,:codpes),:nompes,:nompesttd)";

        $data = [
            'codpes' => 123456,
            'nompes' => 'Fulano da Silva',
            'nompesttd' => 'Fulana da Silva',
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO R25CRECREDOC (codpes, dtavalini, dtavalfim, nivare, codare) VALUES 
                                (convert(int,:codpes),:dtavalini,:dtavalfim,:nivare,convert(int,:codare))";

        $data = [
            'codpes' => 123456,
            'dtavalini' => '2000-10-20 00:00:00',
            'dtavalfim' => '2050-10-20 00:00:00',
            'nivare' => 'PG',
            'codare' => 800,
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertIsArray(Posgraduacao::orientadores(800));
    }

    public function test_catalogoDisciplinas(){
        DB::getInstance()->prepare('DELETE FROM DISCIPLINA')->execute();
        DB::getInstance()->prepare('DELETE FROM R27DISMINCRE')->execute();

        $sql = "INSERT INTO DISCIPLINA (sgldis, numseqdis, nomdis, dtaatvdis) VALUES 
                                (:sgldis,convert(int,:numseqdis),:nomdis,:dtaatvdis)";

        $data = [
            'sgldis' => '800PG',
            'numseqdis' => 1,
            'nomdis' => 'Disciplina de Teste',
            'dtaatvdis' => '2016-10-20',
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO R27DISMINCRE (codare, dtadtvdis, dtaatvdis, sgldis, numseqdis) VALUES 
                                (convert(int,:codare),:dtadtvdis,:dtaatvdis,:sgldis,convert(int,:numseqdis))";

        $data = [
            'codare' => 800,
            'dtadtvdis' => null,
            'dtaatvdis' => '2016-10-20',
            'sgldis' => '800PG',
            'numseqdis' => 1,
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertIsArray(Posgraduacao::catalogoDisciplinas(800));
    }
}