<?php

namespace Uspdev\Replicado\Tests;

use PHPUnit\Framework\TestCase;
use Uspdev\Replicado\Pessoa;
use Uspdev\Replicado\Graduacao;
use Uspdev\Replicado\DB;
use Faker\Factory;

class GraduacaoTest extends TestCase
{
    public function test_nomeHabilitacao(){
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
        $this->assertSame('Inglês',Graduacao::nomeHabilitacao('804', '8051'));
    }

    public function test_nomeCurso(){

        DB::getInstance()->prepare('DELETE FROM CURSOGR')->execute();

        $sql = "INSERT INTO CURSOGR (codcur,nomcur) VALUES 
                                   (convert(int,:codcur),:nomcur)";

        $data = [
            'codcur' => '38',
            'nomcur' => 'História'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('História',Graduacao::nomeCurso('38'));
    }    

    public function test_programa(){

        DB::getInstance()->prepare('DELETE FROM HISTPROGGR')->execute();

        $sql = "INSERT INTO HISTPROGGR (codpes,stapgm,dtaoco) VALUES 
                                   (convert(int,:codpes),:stapgm,convert(datetime,:dtaoco))";

        $data = [
            'codpes' => 420983,
            'stapgm' => true,
            'dtaoco' => '2020-02-02'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertTrue(true,Graduacao::programa('420983'));        
    }

    public function test_curso(){

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

    public function test_verificarCoordenadorCursoGrad(){

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

    public function test_contarAtivosPorGenero(){

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

    public function test_setorAluno(){

        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();
    
        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, codundclg) VALUES 
                                   (convert(int,:codpes),convert(int,:codundclg))";

        $data = [
            'codpes' => 123467,
            'codundclg' => 8,
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertIsArray(Graduacao::setorAluno(123467, 8));   
    }

    public function test_disciplinasEquivalentesCurriculo(){

        DB::getInstance()->prepare('DELETE FROM GRUPOEQUIVGR')->execute();
        DB::getInstance()->prepare('DELETE FROM GRADECURRICULAR')->execute();
        DB::getInstance()->prepare('DELETE FROM EQUIVALENCIAGR')->execute();
        DB::getInstance()->prepare('DELETE FROM CURRICULOGR')->execute();

        $sql = "INSERT INTO GRUPOEQUIVGR (codcrl,codeqv, coddis, verdis) VALUES 
                                   (:codcrl,convert(int,:codeqv),:coddis,convert(smallint, :verdis))";

        $data = [
            'codcrl' => 1202000004331,
            'codeqv' => 2301,
            'coddis' => 'MDF0032',
            'verdis' => 5,
        ];

        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO GRADECURRICULAR (codcrl,tipobg,coddis,verdis) VALUES 
                                   (:codcrl,:tipobg,:coddis,convert(smallint, :verdis))";

        $data = [
            'codcrl' => 1202000004331,
            'tipobg' => 'E',
            'coddis' => 'MDF0032',
            'verdis' => 5,
        ];

        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO EQUIVALENCIAGR (codeqv, coddis, verdis) VALUES 
                                   (convert(int,:codeqv),:coddis,convert(smallint, :verdis))";

        $data = [
            'codeqv' => 2301,
            'coddis' => 'GDT0109',
            'verdis' => 2,
        ];
        
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO CURRICULOGR (codcur, codhab) VALUES 
                                   (convert(int,:codcur),convert(int, :codhab))";

        $data = [
            'codcur' => 3080,
            'codhab' => 1,
        ];        

        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertTrue(true, Graduacao::disciplinasEquivalentesCurriculo(123456, 7));   
    }

}

