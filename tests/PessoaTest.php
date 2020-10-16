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

    public function test_obterCodpesPorEmail(){
        DB::getInstance()->prepare('DELETE FROM EMAILPESSOA')->execute();

        $sql = "INSERT INTO EMAILPESSOA (codpes, codema) VALUES 
                                   (convert(int,:codpes),:codema)";

        $data = [
            'codpes' => 123456,
            'codema' => 'fulana@usp.br'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('123456',Pessoa::obterCodpesPorEmail('fulana@usp.br'));
    }

    public function test_verificarCoordCursosGrad(){
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, nomfnc) VALUES 
                                   (convert(int,:codpes),:nomfnc)";

        $data = [ 
            'codpes' => 123456,
            'nomfnc' => 'Coord'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertTrue(true,Pessoa::verificarCoordCursosGrad('Coord'));
    }

    public function test_verificarEstagioUSP(){
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, tipvin) VALUES 
                                   (convert(int,:codpes),:tipvin)";

        $data = [
            'codpes' => 123456,
            'tipvin' => 'ESTAGIARIO'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertTrue(true,Pessoa::verificarEstagioUSP('ESTAGIARIO'));
    }

    public function test_email(){
        DB::getInstance()->prepare('DELETE FROM EMAILPESSOA')->execute();

        $sql = "INSERT INTO EMAILPESSOA (codpes, stamtr, codema) VALUES 
                                   (convert(int,:codpes),:stamtr,:codema)";

        $data = [
            'codpes' => 123456,
            'stamtr' => 'S',
            'codema' => 'fulana@usp.br'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('fulana@usp.br',Pessoa::email('123456'));
    }

    public function test_emailusp(){
        DB::getInstance()->prepare('DELETE FROM EMAILPESSOA')->execute();

        $sql = "INSERT INTO EMAILPESSOA (codpes, stausp, codema) VALUES 
                                   (convert(int,:codpes),:stausp,:codema)";

        $data = [
            'codpes' => 123456,
            'stausp' => 'S',
            'codema' => 'fulana@usp.br'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('fulana@usp.br',Pessoa::emailusp('123456'));
    }

    public function test_emails(){
        DB::getInstance()->prepare('DELETE FROM EMAILPESSOA')->execute();

        $sql = "INSERT INTO EMAILPESSOA (codpes, codema) VALUES 
                                   (convert(int,:codpes),:codema)";

        $data = [
            'codpes' => 123456,
            'codema' => 'fulana@usp.br'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertIsArray(Pessoa::emails(123456));
    }
}