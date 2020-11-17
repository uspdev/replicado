<?php

namespace Uspdev\Replicado\Tests;

use PHPUnit\Framework\TestCase;
use Uspdev\Replicado\Pessoa;
use Uspdev\Replicado\Graduacao;
use Uspdev\Replicado\DB;
use Faker\Factory;

class GraduacaoTest extends TestCase
{
    public function test_nomeHabilitacao()
    {
        # Limpando Tabela
        DB::getInstance()->prepare('DELETE FROM HABILITACAOGR')->execute();

        $sql = "INSERT INTO HABILITACAOGR (codhab,codcur,nomhab) VALUES 
                                   (convert(int,:codhab),convert(int,:codcur),:nomhab)";

        $data = [
            'codhab' => 804,
            'codcur' => '8051',
            'nomhab' => 'Inglês'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('Inglês', Graduacao::nomeHabilitacao('804', '8051'));
    }

    public function test_nomeCurso()
    {

        DB::getInstance()->prepare('DELETE FROM CURSOGR')->execute();

        $sql = "INSERT INTO CURSOGR (codcur,nomcur) VALUES 
                                   (convert(int,:codcur),:nomcur)";

        $data = [
            'codcur' => '38',
            'nomcur' => 'História'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('História', Graduacao::nomeCurso('38'));
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

    public function test_verificarPessoaGraduadaUnidade()
    {
        // PROGRAMAGR
        DB::getInstance()->prepare('DELETE FROM PROGRAMAGR')->execute();

        $sql = "INSERT INTO PROGRAMAGR (codpgm, codpes, tipencpgm)
                VALUES (convert(int, :codpgm), convert(int, :codpes), :tipencpgm)";

        $data = [
            'codpgm' => 1,
            'codpes' => 123456,
            'tipencpgm' => 'Conclusão'
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
            'tipenchab' => 'Conclusão'
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        putenv('REPLICADO_CODCUR=81003');
        $this->assertTrue(Graduacao::verificarPessoaGraduadaUnidade(123456), 'Graduação não encontrada');
    }
}
