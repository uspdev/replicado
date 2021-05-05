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

        $sql = "INSERT INTO PESSOA (codpes, nompes, nompesttd, dtanas) VALUES 
                                   (convert(int,:codpes),:nompes,:nompesttd,:dtanas)";

        $data = [
            'codpes' => 123456,
            'nompes' => 'Fulano da Silva',
            'nompesttd' => 'Fulana da Silva',
            'dtanas' => '2013-08-06 00:00:00'
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

        $sql = "INSERT INTO PESSOA (codpes, nompes, nompesttd, dtanas) VALUES 
                                (convert(int,:codpes),:nompes,:nompesttd,:dtanas)";

        $data = [
            'codpes' => 123456,
            'nompes' => 'Fulano da Silva',
            'nompesttd' => 'Fulana da Silva',
            'dtanas' => '2018-02-06 00:00:00'
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
            'sgldis' => 'POS800',
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
            'sgldis' => 'POS800',
            'numseqdis' => 1,
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertIsArray(Posgraduacao::catalogoDisciplinas(800));
    }

    public function test_disciplina(){
        DB::getInstance()->prepare('DELETE FROM DISCIPLINA')->execute();

        $sql = "INSERT INTO DISCIPLINA (sgldis, numseqdis, nomdis, dtaatvdis) VALUES 
                                (:sgldis,convert(int,:numseqdis),:nomdis,:dtaatvdis)";

        $data = [
            'sgldis' => 'POS800',
            'numseqdis' => 1,
            'nomdis' => 'Disciplina de Teste',
            'dtaatvdis' => '2016-10-20',
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertIsArray(Posgraduacao::disciplina('POS800'));
    }

    public function test_disciplinasOferecimento(){
        DB::getInstance()->prepare('DELETE FROM DISCIPLINA')->execute();
        DB::getInstance()->prepare('DELETE FROM R27DISMINCRE')->execute();
        DB::getInstance()->prepare('DELETE FROM OFERECIMENTO')->execute();
        DB::getInstance()->prepare('DELETE FROM ESPACOTURMA')->execute();

        $sql = "INSERT INTO DISCIPLINA (sgldis, numseqdis, nomdis, dtaatvdis) VALUES 
                                (:sgldis,convert(int,:numseqdis),:nomdis,:dtaatvdis)";

        $data = [
            'sgldis' => 'POS800',
            'numseqdis' => 1,
            'nomdis' => 'Disciplina de Teste',
            'dtaatvdis' => '2020-10-20',
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO R27DISMINCRE (codare, dtadtvdis, dtaatvdis, sgldis, numseqdis) VALUES 
                                (convert(int,:codare),:dtadtvdis,:dtaatvdis,:sgldis,convert(int,:numseqdis))";

        $data = [
            'codare' => 800,
            'dtadtvdis' => null,
            'dtaatvdis' => '2020-10-20',
            'sgldis' => 'POS800',
            'numseqdis' => 1,
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO OFERECIMENTO (sgldis, numofe, dtainiofe, dtafimofe,numseqdis) VALUES 
                                (:sgldis,convert(int,:numofe),:dtainiofe,:dtafimofe,convert(int,:numseqdis))";

        $data = [
            'sgldis' => 'POS800',
            'numofe' => 1,
            'dtainiofe' => '2020-10-20',
            'dtafimofe' => '2020-12-30',
            'numseqdis' => 1,
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO ESPACOTURMA (sgldis, numseqdis) VALUES 
                                (:sgldis,convert(int,:numseqdis))";

        $data = [
            'sgldis' => 'POS800',
            'numseqdis' => 1,
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertIsArray(Posgraduacao::disciplinasOferecimento(800));
    }

    public function test_oferecimento(){
        DB::getInstance()->prepare('DELETE FROM DISCIPLINA')->execute();
        DB::getInstance()->prepare('DELETE FROM OFERECIMENTO')->execute();

        $sql = "INSERT INTO DISCIPLINA (sgldis, numseqdis, nomdis, dtaatvdis, numcretotdis) VALUES 
                                (:sgldis,convert(int,:numseqdis),:nomdis,:dtaatvdis,convert(int,:numcretotdis))";

        $data = [
            'sgldis' => 'POS800',
            'numseqdis' => 1,
            'nomdis' => 'Disciplina de Teste',
            'dtaatvdis' => '2020-10-20',
            'numcretotdis' => 1,
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO OFERECIMENTO (sgldis, numofe, dtainiofe, dtafimofe, dtalimcan,numseqdis) VALUES 
                                (:sgldis,convert(int,:numofe),:dtainiofe,:dtafimofe,:dtalimcan,convert(int,:numseqdis))";

        $data = [
            'sgldis' => 'POS800',
            'numofe' => 1,
            'dtainiofe' => '2020-10-20',
            'dtafimofe' => '2020-12-30',
            'dtalimcan' => '2020-10-20',
            'numseqdis' => 1,
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertIsArray(Posgraduacao::oferecimento('POS800', 1));
    }

    public function test_espacoturma(){
        DB::getInstance()->prepare('DELETE FROM ESPACOTURMA')->execute();

        $sql = "INSERT INTO ESPACOTURMA (sgldis, numseqdis, numofe, diasmnofe, horiniofe, horfimofe) VALUES 
                                (:sgldis,convert(int,:numseqdis),convert(int,:numofe),:diasmnofe,:horiniofe,:horfimofe)";

        $data = [
            'sgldis' => 'POS800',
            'numseqdis' => 1,
            'numofe' => 1,
            'diasmnofe' => '4QA',
            'horiniofe' => '1200',
            'horfimofe' => '1400',
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $this->assertIsArray(Posgraduacao::espacoturma("POS800", 1, 1));
    }

    public function test_ministrante(){
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();
        DB::getInstance()->prepare('DELETE FROM R32TURMINDOC')->execute();

        $sql = "INSERT INTO PESSOA (codpes, nompes, nompesttd, dtanas) VALUES 
                                (convert(int,:codpes),:nompes,:nompesttd,:dtanas)";

        $data = [
            'codpes' => 123456,
            'nompes' => 'Fulano da Silva',
            'nompesttd' => 'Fulana da Silva',
            'dtanas' => '2018-08-06 00:00:00'
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO R32TURMINDOC (codpes, sgldis, numseqdis, numofe) VALUES 
                                (convert(int,:codpes),:sgldis,convert(int,:numseqdis),convert(int,:numofe))";

        $data = [
            'codpes' => 123456,
            'sgldis' => 'POS800',
            'numseqdis' => 1,
            'numofe' => 1,
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertIsArray(Posgraduacao::ministrante('POS800', 1, 1));
    }

    public function test_areasProgramas(){
        DB::getInstance()->prepare('DELETE FROM AREA')->execute();
        DB::getInstance()->prepare('DELETE FROM NOMEAREA')->execute();
        DB::getInstance()->prepare('DELETE FROM CREDAREA')->execute();

        $sql = "INSERT INTO AREA (codare, codcur) VALUES 
                                (convert(int,:codare),convert(int,:codcur))";

        $data = [
            'codare' => 800,
            'codcur' => 123456,
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO NOMEAREA (codare, codcur, nomare) VALUES 
                                (convert(int,:codare),convert(int,:codcur),:nomare)";

        $data = [
            'codare' => 800,
            'codcur' => 123456,
            'nomare' => 'Humanidades',
        ];
        DB::getInstance()->prepare($sql)->execute($data);

       
        $sql = "INSERT INTO CREDAREA (codare, codcur, dtadtvare) VALUES 
                                (convert(int,:codare),convert(int,:codcur),:dtadtvare)";

        $data = [
            'codare' => 800,
            'codcur' => 123456,
            'dtadtvare' => null,
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertIsArray(Posgraduacao::areasProgramas(8));
    }

    public function test_alunosPrograma(){
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();
        DB::getInstance()->prepare('DELETE FROM VINCULOPESSOAUSP')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, nompes, tipvin, tipvinext, sitatl, codundclg) VALUES 
                                   (convert(int,:codpes),:nompes,:tipvin,:tipvinext,:sitatl,convert(int,:codundclg))";

        $data = [
            'codpes' => 123456,
            'nompes' => 'Fulano da Silva',
            'tipvin' => 'ALUNOPOS',
            'tipvinext' => 'Aluno da Pós-Graduação',
            'sitatl' => 'A',
            'codundclg' => 8,
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO VINCULOPESSOAUSP (codpes, codare, codhab, dtainivin, codcurgrd, nivpgm, tipvin, sitatl) VALUES 
                                   (convert(int,:codpes),convert(int,:codare),convert(int,:codhab),:dtainivin,convert(int,:codcurgrd),:nivpgm,:tipvin,:sitatl)";

        $data = [
            'codpes' => 123456,
            'codare' => 800,
            'codhab' => 2,
            'dtainivin' => '2020-01-01 00:00:00',
            'codcurgrd' => 1,
            'nivpgm' => 'ME',
            'tipvin' => 'ALUNOPOS',
            'sitatl' => 'A',
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertIsArray(Posgraduacao::alunosPrograma(8, 123456));
    }

    public function test_idiomaDisciplina(){
        DB::getInstance()->prepare('DELETE FROM IDIOMA')->execute();

        $sql = "INSERT INTO IDIOMA (codlin, dsclin) VALUES 
                                   (:codlin,:dsclin)";

        $data = [
            'codlin' => 'PT',
            'dsclin' => 'PORTUGUES',
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $this->assertSame($data['dsclin'],Posgraduacao::idiomaDisciplina('PT'));
    }

    public function test_egressosArea(){
        DB::getInstance()->prepare('DELETE FROM HISTPROGRAMA')->execute();
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();
        DB::getInstance()->prepare('DELETE FROM AGPROGRAMA')->execute();
        DB::getInstance()->prepare('DELETE FROM TRABALHOPROG')->execute();

        $sql = "INSERT INTO HISTPROGRAMA (codpes, tiphstpgm, numseqpgm, codare, dtaocopgm) VALUES 
                                (convert(int,:codpes),:tiphstpgm,convert(int,:numseqpgm),convert(int,:codare),:dtaocopgm)";

        $data = [
            'codpes' => 123456,
            'tiphstpgm' => 'con',
            'numseqpgm' => 1,
            'codare' => 800,
            'dtaocopgm' => '2020-06-20',
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO PESSOA (codpes, nompes, nompesttd, dtanas) VALUES 
                                (convert(int,:codpes),:nompes,:nompesttd,:dtanas)";

        $data = [
            'codpes' => 123456,
            'nompes' => 'Fulano da Silva',
            'nompesttd' => 'Fulana da Silva',
            'dtanas' => '2009-08-06 00:00:00'
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO AGPROGRAMA (codpes, codare, numseqpgm, nivpgm, dtadfapgm) VALUES 
                                (convert(int,:codpes),convert(int,:codare),convert(int,:numseqpgm),:nivpgm,:dtadfapgm)";

        $data = [
            'codpes' => 123456,
            'codare' => 800,
            'numseqpgm' => 1,
            'nivpgm' => 'ME',
            'dtadfapgm' => '2020-08-20',
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO TRABALHOPROG (codare, codpes, numseqpgm) VALUES 
                                (convert(int,:codare),convert(int,:codpes),convert(int,:numseqpgm))";

        $data = [
            'codare' => 800,
            'codpes' => 123456,
            'numseqpgm' => 1,
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertIsArray(Posgraduacao::egressosArea(800));
    }

    public function test_contarAtivosPorGenero(){
        DB::getInstance()->prepare('DELETE FROM HISTPROGRAMA')->execute();
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();

        $sql = "INSERT INTO HISTPROGRAMA (codpes, tiphstpgm, numseqpgm, codare, dtaocopgm) VALUES 
                                (convert(int,:codpes),:tiphstpgm,convert(int,:numseqpgm),convert(int,:codare),:dtaocopgm)";

        $data = [
            'codpes' => 123456,
            'tiphstpgm' => 'con',
            'numseqpgm' => 1,
            'codare' => 800,
            'dtaocopgm' => '2020-06-20',
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO PESSOA (codpes, nompes, nompesttd, sexpes, dtanas) VALUES 
                                (convert(int,:codpes),:nompes,:nompesttd,:sexpes,:dtanas)";

        $data = [
            'codpes' => 123456,
            'nompes' => 'Fulano da Silva',
            'nompesttd' => 'Fulana da Silva',
            'sexpes' => 'M',
            'dtanas' => '2019-11-05 00:00:00'
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, nompes, tipvin, tipvinext, sitatl, codundclg, codfncetr) VALUES 
                                   (convert(int,:codpes),:nompes,:tipvin,:tipvinext,:sitatl,convert(int,:codundclg),convert(int,:codfncetr))";

        $data = [
            'codpes' => 123456,
            'nompes' => 'Fulano da Silva',
            'tipvin' => 'ALUNOPOS',
            'tipvinext' => 'Aluno da Pós-Graduação',
            'sitatl' => 'A',
            'codundclg' => 8,
            'codfncetr' => 0
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('1', Posgraduacao::contarAtivosPorGenero('M'));
    }

    public function test_verificarExAlunoPos(){
        DB::getInstance()->prepare('DELETE FROM TITULOPES')->execute();

        $sql = "INSERT INTO TITULOPES (codpes, codorg, codcurpgr) 
                    VALUES (convert(int,:codpes),convert(int,:codorg),convert(int,:codcurpgr))";

        $data = [
            'codpes' => 13131313,
            'codorg' => 7,
            'codcurpgr' => 22
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertTrue(true, Posgraduacao::verificarExAlunoPos(13131313, 7));
    }

    public function test_obterOrientandosConcluidos(){
        DB::getInstance()->prepare('DELETE FROM R39PGMORIDOC')->execute();
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();
        DB::getInstance()->prepare('DELETE FROM NOMEAREA')->execute();
        DB::getInstance()->prepare('DELETE FROM AGPROGRAMA')->execute();

        $sql = "INSERT INTO R39PGMORIDOC (codpes, codpespgm, codare, dtafimort) 
                    VALUES (convert(int,:codpes),convert(int,:codpespgm),convert(int,:codare),:dtafimort)";

        $data = [
            'codpes' => 11698748, #Número USP do docente, a ser passado no parâmetro.
            'codpespgm' => 123695, #Número USP do orientando, a ser retornado.
            'codare' => 6,
            'dtafimort' => '2019-11-05 00:00:00',
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO PESSOA (codpes, nompes, nompesttd, nompesfon, sexpes, dtanas) 
                    VALUES (convert(int,:codpes),:nompes,:nompesttd,:nompesfon,:sexpes, :dtanas)";

        $data = [
            'codpes'    => 123695, #Número USP do orientando, a ser retornado.
            'nompes'    => 'Henry',
            'nompesttd' => 'Henry',
            'nompesfon' => 'Henry',
            'sexpes'    => 'M',
            'dtanas'    => '1986-11-05 00:00:00',
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO NOMEAREA (codare, nomare,codcur, dtafimare) 
                    VALUES (convert(int,:codare),:nomare,convert(int,:codcur),:dtafimare)";

        $data = [
            'codare' => 6,
            'nomare' => 'Estudo',
            'codcur' => 123,
            'dtafimare' => '2019-11-05 00:00:00'            
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO AGPROGRAMA (codpes, dtadfapgm, nivpgm, codare) 
                    VALUES (convert(int,:codpes),:dtadfapgm,:nivpgm, convert(int,:codare))";

        $data = [
            'codpes' => 123695, #Número USP do orientando, a ser retornado.
            'codare' => 6,
            'dtadfapgm' => '2019-11-05 00:00:00',
            'nivpgm' => 'MO'            
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $this->assertSame('Henry', Posgraduacao::obterOrientandosConcluidos(11698748)[0]['nompes']);
    }
}
