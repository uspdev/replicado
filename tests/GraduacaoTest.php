<?php

namespace Uspdev\Replicado\Tests;

use PHPUnit\Framework\TestCase;
use Uspdev\Replicado\Pessoa;
use Uspdev\Replicado\Graduacao;
use Uspdev\Replicado\DB;

class GraduacaoTest extends TestCase
{
    public function test_verifica()
    {
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (tipvin,sitatl,codundclg,codpes) VALUES 
                                   (:tipvin,:sitatl,convert(smallint,:codundclg),convert(int,:codpes))";

        $data = [
            'tipvin' => 'ALUNOGR',
            'sitatl' => 'A',
            'codundclg' => '2',
            'codpes' => 4509883,
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertTrue(true, Graduacao::verifica(4509883, 2));
    }

    public function test_ativos()
    {
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codundclg,tipvin,nompesfon) VALUES 
                                   (convert(smallint,:codundclg),:tipvin,:nompesfon)";

        $data = [
            'codundclg' => '2',
            'tipvin'  => 'ALUNOGR',
            'nompesfon' => 'Jorge Almeida',
        ];

        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertIsArray(Graduacao::ativos(2, 'Jorge'));
    }

    public function test_obterCursosHabilitacoes()
    {
        DB::getInstance()->prepare('DELETE FROM CURSOGR')->execute();
        DB::getInstance()->prepare('DELETE FROM HABILITACAOGR')->execute();

        $sql = "INSERT INTO CURSOGR (codclg,codcur,dtaatvcur) VALUES 
                                   (convert(smallint,:codclg),convert(int,:codcur),convert(smalldatetime,:dtaatvcur))";

        $data = [
            'codclg' => '2',
            'codcur' => '3010',
            'dtaatvcur' => '2010-10-10',
        ];

        DB::getInstance()->prepare($sql)->execute($data);


        $sql = "INSERT INTO HABILITACAOGR (codcur,dtaatvhab) VALUES 
                                   (convert(int,:codcur),convert(smalldatetime,:dtaatvhab))";

        $data = [
            'codcur' => '3010',
            'dtaatvhab' => '2005-09-10',
        ];

        DB::getInstance()->prepare($sql)->execute($data);

        $this->assertIsArray(Graduacao::obterCursosHabilitacoes(2));
    }

    public function test_obterDisciplinas()
    {
        DB::getInstance()->prepare('DELETE FROM DISCIPLINAGR')->execute();

        $sql = "INSERT INTO DISCIPLINAGR (verdis,coddis,nomdis) VALUES 
                                   (convert(tinyint,:verdis),:coddis,:nomdis)";

        $data1 = [
            'verdis' => '2',
            'coddis' => 'JOR0031',
            'nomdis' => 'Disciplina 1'
        ];
        $data2 = [
            'verdis' => '5',
            'coddis' => 'TLF0023',
            'nomdis' => 'Disciplina 2'
        ];
        $data3 = [
            'verdis' => '1',
            'coddis' => 'TLC0023',
            'nomdis' => 'Disciplina 3'
        ];

        DB::getInstance()->prepare($sql)->execute($data1);
        DB::getInstance()->prepare($sql)->execute($data2);
        DB::getInstance()->prepare($sql)->execute($data3);

        $array = ['JOR0031', 'TLC0023', 'TLF0023'];
        $this->assertSame('Disciplina 1', Graduacao::obterDisciplinas($array)[0]['nomdis']);
        $this->assertIsArray(Graduacao::obterDisciplinas($array));
    }

    public function test_nomeHabilitacao()
    {
        # Limpando Tabela
        DB::getInstance()->prepare('DELETE FROM HABILITACAOGR')->execute();

        $sql = "INSERT INTO HABILITACAOGR (codhab,codcur,nomhab,dtaatvhab) VALUES 
                                   (convert(smallint,:codhab),convert(int,:codcur),:nomhab,convert(smalldatetime,:dtaatvhab))";

        $data = [
            'codhab' => 804,
            'codcur' => 8051,
            'nomhab' => 'Alemão',
            'dtaatvhab' => '2005-09-10',
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('Alemão', Graduacao::nomeHabilitacao(804, 8051));
    }

    public function test_nomeCurso()
    {

        DB::getInstance()->prepare('DELETE FROM CURSOGR')->execute();

        $sql = "INSERT INTO CURSOGR (codcur,nomcur,codclg,dtaatvcur) VALUES 
                                   (convert(int,:codcur),:nomcur,convert(smallint,:codclg),convert(smalldatetime,:dtaatvcur))";

        $data = [
            'codcur' => 38,
            'nomcur' => 'Letras',
            'codclg' => '123',
            'dtaatvcur' => '2010-10-10',
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('Letras', Graduacao::nomeCurso(38));
    }

    public function test_programa()
    {

        DB::getInstance()->prepare('DELETE FROM HISTPROGGR')->execute();

        $sql = "INSERT INTO HISTPROGGR (codpes,stapgm,dtaoco) VALUES 
                                   (convert(int,:codpes),:stapgm,convert(datetime,:dtaoco))";

        $data = [
            'codpes' => 420983,
            'stapgm' => true,
            'dtaoco' => '2020-02-02'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertTrue(true, Graduacao::programa('420983'));
    }

    public function test_nomeDisciplina()
    {

        DB::getInstance()->prepare('DELETE FROM DISCIPLINAGR')->execute();

        $sql = "INSERT INTO DISCIPLINAGR (coddis, verdis, nomdis) VALUES 
                                   (:coddis,convert(tinyint,:verdis),:nomdis)";

        $data = [
            'coddis' => 'TLC0023',
            'verdis' => '3',
            'nomdis' => 'Arqueologia Mesopotamica',
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('Arqueologia Mesopotamica', Graduacao::nomeDisciplina('TLC0023'));
    }


    public function test_creditosDisciplina()
    {

        DB::getInstance()->prepare('DELETE FROM DISCIPLINAGR')->execute();

        $sql = "INSERT INTO DISCIPLINAGR (coddis, verdis, nomdis, creaul) VALUES 
                                   (:coddis,convert(tinyint,:verdis),:nomdis,convert(tinyint,:creaul))";

        $data = [
            'coddis' => 'TLC0023',
            'verdis' => '3',
            'nomdis' => 'Arqueologia Mesopotamica',
            'creaul' => '12',
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('12', Graduacao::creditosDisciplina('TLC0023'));
    }

    public function test_curso()
    {

        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, codundclg) VALUES 
                                   (convert(int,:codpes),convert(int,:codundclg))";

        $data = [
            'codpes' => 420983,
            'codundclg' => 7,
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertTrue(true, Graduacao::curso(123456, 7));
    }

    public function test_verificarCoordenadorCursoGrad()
    {

        DB::getInstance()->prepare('DELETE FROM CURSOGRCOORDENADOR')->execute();

        $sql = "INSERT INTO CURSOGRCOORDENADOR (codpesdct, dtainicdn, dtafimcdn) VALUES 
                                   (convert(int,:codpesdct), convert(smalldatetime,:dtainicdn), convert(smalldatetime,:dtafimcdn))";

        $data = [
            'codpesdct' => 333444,
            'dtainicdn' => '2020-03-14 00:00:00',
            'dtafimcdn' => '2021-03-13 00:00:00',
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertTrue(true, Graduacao::verificarCoordenadorCursoGrad(333444));
    }

    public function test_contarAtivosPorGenero()
    {

        DB::getInstance()->prepare('DELETE FROM SITALUNOATIVOGR')->execute();
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();

        $sql = "INSERT INTO PESSOA (sexpes) VALUES 
                                   (:sexpes)";

        $data = [
            'sexpes' => 'M',
        ];

        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO SITALUNOATIVOGR (codcur) VALUES 
                                   (convert(int,:codcur))";

        $data = [
            'codcur' => 4,
        ];

        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('0', Graduacao::contarAtivosPorGenero('M', 4));
    }

    public function test_setorAluno()
    {

        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();
        DB::getInstance()->prepare('DELETE FROM CURSOGRCOORDENADOR')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, nomabvset) VALUES 
                                   (convert(int,:codpes),:nomabvset)";

        $data = [
            'codpes' => 123467,
            'nomabvset' => "historia",
        ];

        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO CURSOGRCOORDENADOR (codpesdct, codcur, codhab, dtainicdn) VALUES 
                                   (convert(int,:codpesdct),convert(int,:codcur),convert(smallint,:codhab),convert(smalldatetime,:dtainicdn))";

        $data = [
            'codpesdct' => 123467,
            'codcur' => 3456,
            'codhab' => 21,
            'dtainicdn' => '2020-03-14',
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $this->assertIsArray(Graduacao::setorAluno(123467, 8));
    }

    public function test_disciplinasConcluidas()
    {
        DB::getInstance()->prepare('DELETE FROM HISTESCOLARGR')->execute();
        DB::getInstance()->prepare('DELETE FROM DISCIPLINAGR')->execute();

        $sql = "INSERT INTO HISTESCOLARGR (codpes,codpgm,coddis,verdis,codtur,rstfim,stamtr) VALUES 
                                   (convert(int,:codpes),convert(tinyint,:codpgm),:coddis,convert(tinyint,:verdis),:codtur,:rstfim,:stamtr)";

        $data = [
            'codpes' => '123467',
            'codpgm' => '11',
            'coddis' => 'CED0043',
            'verdis' => '3',
            'codtur' => '0',
            'rstfim' => 'A',
            'stamtr' => 'A',
        ];

        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO DISCIPLINAGR (coddis,verdis,creaul,cretrb) VALUES 
                                   (:coddis,convert(tinyint,:verdis),convert(tinyint,:creaul),convert(tinyint,:cretrb))";

        $data = [
            'coddis' => 'CED0043',
            'verdis' => '3',
            'creaul' => '12',
            'cretrb' => '4',
        ];

        DB::getInstance()->prepare($sql)->execute($data);

        $this->assertIsArray(Graduacao::disciplinasConcluidas(123467, 8));
    }

    public function test_creditosDisciplinasConcluidasAproveitamentoEstudosExterior()
    {

        DB::getInstance()->prepare('DELETE FROM DISCIPLINAGR')->execute();
        DB::getInstance()->prepare('DELETE FROM HISTESCOLARGR')->execute();
        DB::getInstance()->prepare('DELETE FROM REQUERHISTESC')->execute();

        $sql = "INSERT INTO REQUERHISTESC (codpes,coddis,verdis,codtur,creaulatb) VALUES 
                                    (convert(int,:codpes),:coddis,convert(tinyint,:verdis),:codtur,convert(tinyint,:creaulatb))";

        $data = [
            'codpes' => '123467',
            'coddis' => 'CED0043',
            'verdis' => '3',
            'codtur' => '0',
            'creaulatb' => '10',
        ];

        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO HISTESCOLARGR (codpes,codpgm,coddis,verdis,codtur,rstfim) VALUES 
                                   (convert(int,:codpes),convert(tinyint,:codpgm),:coddis,convert(tinyint,:verdis),:codtur,:rstfim)";

        $data = [
            'codpes' => '123467',
            'codpgm' => '11',
            'coddis' => 'CED0043',
            'verdis' => '3',
            'codtur' => '0',
            'rstfim' => 'D',
        ];

        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO DISCIPLINAGR (coddis,verdis) VALUES 
                                   (:coddis,convert(tinyint,:verdis))";

        $data = [
            'coddis' => 'CED0043',
            'verdis' => '3',
        ];

        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertIsArray(Graduacao::creditosDisciplinasConcluidasAproveitamentoEstudosExterior(123467, 8));
    }

    public function test_obterGradeHoraria()
    {
        DB::getInstance()->prepare('DELETE FROM HISTESCOLARGR')->execute();
        DB::getInstance()->prepare('DELETE FROM OCUPTURMA')->execute();
        DB::getInstance()->prepare('DELETE FROM PERIODOHORARIO')->execute();

        $sql = "INSERT INTO HISTESCOLARGR (codpes,coddis,codtur) VALUES 
                                    (convert(int,:codpes),:coddis,:codtur)";

        $data = [
            'codpes' => '123467',
            'coddis' => 'CED0043',
            'codtur' => 1997103
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO OCUPTURMA (coddis,codtur, diasmnocp, codperhor) VALUES 
                                    (:coddis,:codtur,:diasmnocp,convert(numeric,:codperhor))";

        $data = [
            'coddis' => 'CED0043',
            'codtur' => 0,
            'diasmnocp' => 'sex',
            'codperhor' => 72
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO PERIODOHORARIO (codperhor, horent, horsai) VALUES 
                                    (convert(numeric,:codperhor),:horent,:horsai)";

        $data = [
            'codperhor' => 1,
            'horent' => '07:00',
            'horsai' => '09:00'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertIsArray(Graduacao::obterGradeHoraria(123467));
    }


    public function test_obterCodigosCursos()
    {
        // CURSOGR
        DB::getInstance()->prepare('DELETE FROM CURSOGR')->execute();

        $sql = "INSERT INTO CURSOGR (codcur,nomcur,codclg,dtaatvcur) VALUES 
                                   (convert(int,:codcur),:nomcur,convert(smallint,:codclg),convert(smalldatetime,:dtaatvcur))";

        $data = [
            'codcur' => 81003,
            'nomcur' => 'Bacharelado em Administração',
            'codclg' => '8',
            'dtaatvcur' => '2011-01-01',
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO CURSOGR (codcur,nomcur,codclg,dtaatvcur) VALUES 
                                   (convert(int,:codcur),:nomcur,convert(smallint,:codclg),convert(smalldatetime,:dtaatvcur))";

        $data = [
            'codcur' => 81002,
            'nomcur' => 'Bacharelado em Administração',
            'codclg' => '8',
            'dtaatvcur' => '2001-01-01',
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $this->assertContains('81003', Graduacao::obterCodigosCursos());
        $this->assertContains('81002', Graduacao::obterCodigosCursos());
    }

    public function test_verificarPessoaGraduadaUnidade()
    {
        // PROGRAMAGR
        DB::getInstance()->prepare('DELETE FROM PROGRAMAGR')->execute();

        $sql = "INSERT INTO PROGRAMAGR (codpgm, codpes, tipencpgm)
                VALUES (convert(int, :codpgm), convert(int, :codpes), :tipencpgm)";

        $data = [
            'codpgm' => 1,
            'codpes' => 123456,
            'tipencpgm' => 'Conclusao' //adicionado sem acento por problema de codificação
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        // HABILPROGGR
        DB::getInstance()->prepare('DELETE FROM HABILPROGGR')->execute();
        $sql = "INSERT INTO HABILPROGGR (codpgm, codpes, codcur, codhab, dtaclcgru, tipenchab)
                VALUES (convert(int, :codpgm), convert(int, :codpes), convert(int, :codcur), convert(int, :codhab), :dtaclcgru, :tipenchab)";

        $data = [
            'codpgm' => 1,
            'codpes' => 123456,
            'codcur' => 81003,
            'codhab' => 4,
            'dtaclcgru' => '2012-02-02 00:00:00',
            'tipenchab' => 'Termino' //adicionado sem acento por problema de codificação
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $this->assertTrue(Graduacao::verificarPessoaGraduadaUnidade(123456), 'Graduação não encontrada');
    }
}
