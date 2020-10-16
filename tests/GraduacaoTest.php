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
            'codhab' => '804',
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

}

