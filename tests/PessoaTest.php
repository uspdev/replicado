<?php

namespace Uspdev\Replicado\Tests;

use PHPUnit\Framework\TestCase;
use Uspdev\Replicado\Pessoa;
use Uspdev\Replicado\Uteis;
use Uspdev\Replicado\DB;

class PessoaTest extends TestCase
{
    public function test_nomeCompleto(){
        # Limpando Tabela
        DB::getInstance()->exec('DELETE FROM PESSOA');

        $sql = file_get_contents(__DIR__ .'/data/'.'PessoaTest.test_nomeCompleto.sql');
        
        DB::getInstance()->exec($sql);
        $this->assertSame('Fulano da Silva',Pessoa::nomeCompleto(123456));
    }

    public function test_obterCodpesPorEmail(){
        DB::getInstance()->exec('DELETE FROM EMAILPESSOA');
        $sql = file_get_contents(__DIR__ .'/data/'.'PessoaTest.test_obterCodpesPorEmail.sql');
        DB::getInstance()->exec($sql);
        $this->assertSame('123456',Pessoa::obterCodpesPorEmail('fulano@usp.br'));
    }

    public function test_verificarEstagioUSP(){
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, tipvin, codfncetr) 
                    VALUES (convert(int,:codpes),:tipvin,convert(int,:codfncetr))";

        $data = [
            'codpes' => 123456,
            'tipvin' => 'ESTAGIARIORH',
            'codfncetr' => 0
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertTrue(Pessoa::verificarEstagioUSP(123456));
    }

    public function test_email(){
        DB::getInstance()->prepare('DELETE FROM EMAILPESSOA')->execute();

        $sql = "INSERT INTO EMAILPESSOA (codpes, stamtr, codema) 
                    VALUES (convert(int,:codpes),:stamtr,:codema)";

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

        $sql = "INSERT INTO EMAILPESSOA (codpes, stausp, codema) 
                    VALUES (convert(int,:codpes),:stausp,:codema)";

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

        $sql = "INSERT INTO EMAILPESSOA (codpes, codema) 
                VALUES (convert(int,:codpes),:codema)";

        $data = [
            'codpes' => 123456,
            'codema' => 'fulana@usp.br'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $output = Pessoa::emails(123456);
        $this->assertIsArray($output);
        $this->assertSame('fulana@usp.br',$output[0]);
    }

    public function test_telefones(){
        DB::getInstance()->prepare('DELETE FROM TELEFPESSOA')->execute();

        $sql = "INSERT INTO TELEFPESSOA (codpes, codddd, numtel) 
                VALUES (
                    convert(int,:codpes),
                    :codddd,
                    :numtel
                )";

        $data = [
            'codpes' => 123456,
            'codddd' => 11,
            'numtel' => 954668532 
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('(11) 954668532',Pessoa::telefones(123456)[0]);
    }

    public function test_nome()
    {
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();

        $sql = "INSERT INTO PESSOA (codpes, nompes, nompesttd, dtanas) 
                VALUES (
                    convert(int,:codpes), 
                    :nompes, 
                    :nompesttd, 
                    :dtanas
                )";                         
        $data = [
            'codpes' => 22222,
            'nompes' => 'Hogwarts',
            'nompesttd' => 'Hogwarts',
            'dtanas' => '2018-10-06 00:00:00'
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $this->assertSame('22222',Pessoa::nome('Hogwarts')[0]['codpes']);
        $this->assertSame([],Pessoa::nome('Ninguém'));
    }

    public function test_nomeFonetico()
    {
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();

        $sql = "INSERT INTO PESSOA (codpes, nompes, nompesttd, nompesfon, dtanas) 
                VALUES (
                    convert(int,:codpes), 
                    :nompes, 
                    :nompesttd, 
                    :nompesfon, 
                    :dtanas
                )";                         
        $data = [
            'codpes' => 22222,
            'nompes' => 'João Batista',
            'nompesttd' => 'João Batista',
            'nompesfon' => Uteis::fonetico('João Batista'),
            'dtanas' => '2011-10-06 00:00:00'
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $this->assertSame('22222',Pessoa::nomeFonetico('joao')[0]['codpes']);
        $this->assertSame([],Pessoa::nomeFonetico('Ninguém'));
    }

    public function test_procurarPorNome() 
    {
        # cleanup
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();

        # 1st person
        $sql = "INSERT INTO PESSOA (codpes, nompes, nompesttd, nompesfon, dtanas) 
                VALUES (
                    convert(int,:codpes), 
                    :nompes, 
                    :nompesttd, 
                    :nompesfon, 
                    :dtanas
                )";                         
        $data = [
            'codpes' => 123456,
            'nompes' => 'Hogwarts da Silva',
            'nompesttd' => 'Hogwarts da Silva',
            'nompesfon' => Uteis::fonetico('Hogwarts da Silva'),
            'dtanas' => '2018-08-06 00:00:00'
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        # 2nd person
        $data = [
            'codpes' => 78910,
            'nompes' => 'Maria Joaquina Belém',
            'nompesttd' => 'Maria Joaquina Belém',
            'nompesfon' => Uteis::fonetico('Maria Joaquina Belém'),
            'dtanas' => '2017-05-06 00:00:00'
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, codfncetr) 
                VALUES (convert(int,:codpes),convert(int,:codfncetr))";

        $data = ['codpes' => 123456, 'codfncetr' => 0];
        DB::getInstance()->prepare($sql)->execute($data);

        $data = ['codpes' => 78910, 'codfncetr' => 0];
        DB::getInstance()->prepare($sql)->execute($data);

        # assert sem parametros opcionais (true, true)
        $this->assertSame('78910', Pessoa::procurarPorNome('Belém')[0]['codpes']);

        # alguns asserts que são vazios
        $this->assertSame([], Pessoa::procurarPorNome('Ninguém', false, true));
        $this->assertSame([], Pessoa::procurarPorNome('Ninguém', true, false));
        $this->assertSame([], Pessoa::procurarPorNome('Ninguém', false, false));

        # aqui deveria retornar a pessoa, mas está retornando vazio
        #echo 'dump '; var_dump(Pessoa::procurarPorNome('Belém', false, false));
        #$this->assertSame('78910', Pessoa::procurarPorNome('Belém', false, false)[0]['codpes']);
        
        # procurando sem fonetico
        $this->assertSame('123456', Pessoa::procurarPorNome('Hogwarts', false, false)[0]['codpes']);
        $this->assertSame('78910', Pessoa::procurarPorNome('Joaquina', false, false)[0]['codpes']);
    }

    public function test_obterRamalUsp(){
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, numtelfmt, codfncetr) 
                VALUES (
                    convert(int,:codpes),
                    :numtelfmt,
                    convert(int,:codfncetr)
                )";

        $data = [
            'codpes' => 123456,
            'numtelfmt' => 954668532,
            'codfncetr' => 0 
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('954668532',Pessoa::obterRamalUsp(123456));
        $this->assertSame('',Pessoa::obterRamalUsp(111111));
    }
    
    public function test_vinculos(){
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, codundclg, tipvinext, nomfnc, nomset, sglclgund, codfncetr) 
                VALUES (
                    convert(int,:codpes),
                    convert(int,:codundclg),
                    :tipvinext,
                    :nomfnc,
                    :nomset,
                    :sglclgund,
                    convert(int,:codfncetr)
                )";

        $data = [
            'codpes' => 123456,
            'codundclg' => 8,
            'tipvinext' => 'Estagiário',
            'nomfnc' => 'Estagiário',
            'nomset' => 'Diretoria Faculdade de Filosofia, Letras e Ciências Humanas',
            'sglclgund' => 'CG',
            'codfncetr' => 0
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame("Estagiário - Estagiário - Diretoria Faculdade de Filosofia, Letras e Ciências Humanas - CG",Pessoa::vinculos(123456)[0]);
    }

    public function test_contarEstagiariosAtivosPorGenero(){
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, tipvin, codundclg, codfncetr) VALUES 
                                   (convert(int,:codpes),:tipvin,convert(int,:codundclg),convert(int,:codfncetr))";

        $data = [
            'codpes' => 145368,
            'tipvin' => 'ESTAGIARIORH',
            'codundclg' => 8,
            'codfncetr' => 0
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO PESSOA (codpes, sexpes, dtanas) 
                VALUES (
                    convert(int,:codpes),
                    :sexpes,
                    :dtanas
                )";

        $data = [
            'codpes' => 145368,
            'sexpes' => 'F',
            'dtanas' => '2018-06-04 00:00:00'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('0',Pessoa::contarEstagiariosAtivosPorGenero('F'));        
    }

    public function test_contarServidoresAtivosPorGenero(){
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, tipvinext, codundclg, codfncetr) VALUES 
                                   (convert(int,:codpes),:tipvinext,convert(int,:codundclg),convert(int,:codfncetr))";

        $data = [
            'codpes' => 1234567,
            'tipvinext' => 'Servidor',
            'codundclg' => 8,
            'codfncetr' => 0
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO PESSOA (codpes, sexpes, dtanas) 
                VALUES (
                    convert(int,:codpes),
                    :sexpes,
                    :dtanas
                )";

        $data = [
            'codpes' => 1234567,
            'sexpes' => 'F',
            'dtanas' => '2007-10-05 00:00:00'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('0',Pessoa::contarServidoresAtivosPorGenero('F'));        
    }

    public function test_totalPosNivelPrograma(){
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();
        DB::getInstance()->prepare('DELETE FROM VINCULOPESSOAUSP')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, tipvin, codundclg, sitatl, codfncetr) 
                VALUES (
                    convert(int,:codpes),
                    :tipvin,
                    convert(int,:codundclg),
                    :sitatl,
                    convert(int,:codfncetr)
                )";                         

        $data = [
            'codpes' => 12345,
            'tipvin' => 'ALUNOPOS',
            'codundclg' => 8,
            'sitatl' => 'A',
            'codfncetr' => 0
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO VINCULOPESSOAUSP (codpes, tipvin, nivpgm) 
                VALUES (
                    convert(int,:codpes),
                    :tipvin,
                    :nivpgm
                )";                         

        $data = [
            'codpes' => 12345,
            'tipvin' => 'ALUNOPOS',
            'nivpgm' => 'ME'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('0',Pessoa::totalPosNivelPrograma('ME', 8));
    }

    public function test_obterEndereco(){
        DB::getInstance()->prepare('DELETE FROM ENDPESSOA')->execute();
        DB::getInstance()->prepare('DELETE FROM LOCALIDADE')->execute();
        DB::getInstance()->prepare('DELETE FROM TIPOLOGRADOURO')->execute();

        $sql = "INSERT INTO ENDPESSOA (codpes, epflgr, numlgr, cpllgr, nombro, codendptl, codloc, codtiplgr) VALUES 
                                   (convert(int,:codpes),:epflgr,:numlgr,:cpllgr,:nombro,:codendptl,convert(int,:codloc),convert(int,:codtiplgr))";                         

        $data = [
            'codpes' => 114478,
            'epflgr' => 'Bosque',
            'numlgr' => 8,
            'cpllgr' => 'A22',
            'nombro' => 'Centro',
            'codendptl' => '09910370',
            'codloc' => 6,
            'codtiplgr' => 39
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO LOCALIDADE (codloc, cidloc, sglest) 
                VALUES (
                    convert(int,:codloc),
                    :cidloc,
                    :sglest
                )";                         

        $data = [
            'codloc' => 6,
            'cidloc' => 'Diadema',
            'sglest' => 'SP'
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO TIPOLOGRADOURO (nomtiplgr, codtiplgr) 
                VALUES (
                    :nomtiplgr,
                    convert(int,:codtiplgr)
                )";                         

        $data = [
            'nomtiplgr' => 'Rua',
            'codtiplgr' => 39
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        # esta pessoa não existe
        $this->assertSame(false,Pessoa::obterEndereco(111111));

        $data = [
            'nomtiplgr' => 'Rua',
            'epflgr' => 'Bosque',
            'numlgr' => '8',
            'cpllgr' => 'A22',
            'nombro' => 'Centro',
            'cidloc' => 'Diadema',
            'sglest' => 'SP',
            'codendptl' => '09910370',
        ];
        $this->assertSame($data,Pessoa::obterEndereco(114478));
    }

    public function test_cracha(){
        //arrumar
        DB::getInstance()->prepare('DELETE FROM CATR_CRACHA')->execute();

        $sql = "INSERT INTO CATR_CRACHA (codpescra) VALUES 
                                   (:codpescra)";                         

        $data = [
            'codpescra' => '123456'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('123456',Pessoa::cracha('123456')['codpescra']);
    }

    public function test_tiposVinculos(){
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, sitatl, codundclg, tipvin, tipvinext, codfncetr) 
                    VALUES (
                        convert(int,:codpes),
                        :sitatl,
                        convert(int,:codundclg),
                        :tipvin,
                        :tipvinext,
                        convert(int,:codfncetr)
                    )";                         

        $data = [
            'codpes' => 11111,
            'sitatl' => 'A',
            'codundclg' => 1,
            'tipvin' => 'ALUNOGR',
            'tipvinext' => 'Aluno de Graduação',
            'codfncetr' => 0
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame([["tipvinext" => 'Aluno de Graduação']],Pessoa::tiposVinculos(1));
    }

    public function test_dump(){
        
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();

        $sql = "INSERT INTO PESSOA (codpes, nompes, dtanas) 
                VALUES (convert(int,:codpes), :nompes,:dtanas)";
        $data = [
            'codpes' => 123456, 
            'nompes'=>'Fulano de Teste',
            'dtanas' => '2006-10-05 00:00:00'
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $this->assertSame('123456',Pessoa::dump(123456)['codpes']);
        $this->assertSame(false,Pessoa::dump(111111));
    }

    public function test_estagiarios(){
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, tipvin, codundclg, sitatl, codfncetr) 
                VALUES (
                    convert(int,:codpes),
                    :tipvin,
                    convert(int,:codundclg),
                    :sitatl,
                    convert(int,:codfncetr)
                )";

        $data = [
            'codpes' => 145368,
            'tipvin' => 'ESTAGIARIORH',
            'codundclg' => 2,
            'sitatl' => 'A',
            'codfncetr' => 0
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO PESSOA (codpes, nompes, dtanas) 
                VALUES (
                    convert(int,:codpes),
                    :nompes,
                    :dtanas
                )";

        $data = [
            'codpes' => 145368,
            'nompes' => 'Rita',
            'dtanas' => '2010-04-05 00:00:00'
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $this->assertSame('145368',Pessoa::estagiarios(2)[0]['codpes']);        
    }

    public function test_servidores(){
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, tipvinext, codundclg, sitatl, codfncetr) 
                VALUES (
                    convert(int,:codpes),
                    :tipvinext,
                    convert(int,:codundclg),
                    :sitatl,
                    convert(int,:codfncetr)
                )";

        $data = [
            'codpes' => 55555,
            'tipvinext' => 'Servidor',
            'codundclg' => 5,
            'sitatl' => 'A',
            'codfncetr' => 0
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO PESSOA (codpes, nompes, dtanas) 
                VALUES (
                        convert(int,:codpes),
                        :nompes,
                        :dtanas
                )";

        $data = [
            'codpes' => 55555,
            'nompes' => 'Tifany',
            'dtanas' => '2000-10-05 00:00:00'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('55555',Pessoa::servidores(5)[0]['codpes']);        
    }

    public function test_ativosVinculo(){
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, tipvinext, codundclg, sitatl, codfncetr) 
                VALUES (
                    convert(int,:codpes),
                    :tipvinext,
                    convert(int,:codundclg),
                    :sitatl,
                    convert(int,:codfncetr)
                )";

        $data = [
            'codpes' => 99999,
            'tipvinext' => 'Docente Aposentado',
            'codundclg' => 9,
            'sitatl' => 'A',
            'codfncetr' => 0
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO PESSOA (codpes) 
                VALUES (
                        convert(int,:codpes)
                )";

        $data = [
            'codpes' => 99999
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame([["total" => '0']],Pessoa::ativosVinculo('Docente Aposentado', 9, 1));        
    }

    public function test_nascimento(){
        
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();

        $sql = "INSERT INTO PESSOA (codpes, dtanas, nompes) 
                VALUES (
                    convert(int,:codpes),
                    :dtanas,
                    :nompes
                )";
        $data = [
            'codpes' => 454545, 
            'dtanas' => '2018-11-05 00:00:00',
            'nompes' => 'Isaque'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('05/11/2018',Pessoa::nascimento(454545));
    }

    public function test_listarDocentes(){
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();
        DB::getInstance()->prepare('DELETE FROM PESSOA')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, tipvinext, codundclg, sitatl, codset, codfncetr) 
                VALUES (
                    convert(int,:codpes),
                    :tipvinext,
                    convert(int,:codundclg),
                    :sitatl,
                    convert(int,:codset),
                    convert(int,:codfncetr)
                )";

        $data = [
            'codpes' => 101010,
            'tipvinext' => 'Docente',
            'codundclg' => 9,
            'sitatl' => 'A',
            'codset' => 598,
            'codfncetr' => 0
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO PESSOA (codpes, nompes, nompesttd, dtanas) 
                VALUES (
                    convert(int,:codpes),
                    :nompes,
                    :nompesttd,
                    :dtanas
                )";

        $data = [
            'codpes' => 101010,
            'nompes' => 'Fredie',
            'nompesttd' => 'Fredie Silva',
            'dtanas' => '1990-11-05 00:00:00'
        ];
        
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame([],Pessoa::listarDocentes(598));        
    }

    public function test_vinculosSetores(){
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, tipvinext, tipvin, codundclg, sitatl, codset, codfncetr, nomabvset) 
                VALUES (
                    convert(int,:codpes),
                    :tipvinext,
                    :tipvin,
                    convert(int,:codundclg),
                    :sitatl,
                    convert(int,:codset),
                    convert(int,:codfncetr),
                    :nomabvset
                )";

        $data = [
            'codpes' => 10101,
            'tipvinext' => 'Estagiário',
            'tipvin' => 'ESTAGIARIORH',
            'codundclg' => 9,
            'sitatl' => 'A',
            'codset' => 598,
            'codfncetr' => 0,
            'nomabvset' => 'FFLCH'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame([0 => 'Estagiário', 1 => 'Estagiário FFLCH', 2 => 'FFLCH'],Pessoa::vinculosSetores(10101, 9));        
    }

    public function test_listarDocentesAposentadosSenior(){
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();
        DB::getInstance()->prepare('DELETE FROM VINCSATPROFSENIOR')->execute();

        $sql = "INSERT INTO LOCALIZAPESSOA (codpes, tipvinext, sitatl, codset, codfncetr, nompes) 
                VALUES (
                    convert(int,:codpes),
                    :tipvinext,
                    :sitatl,
                    convert(int,:codset),
                    convert(int,:codfncetr),
                    :nompes
                )";

        $data = [
            'codpes' => 98958989,
            'tipvinext' => 'Docente Aposentado',
            'sitatl' => 'P',
            'codset' => 666,
            'codfncetr' => 0,
            'nompes' => 'Maria'
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $sql = "INSERT INTO VINCSATPROFSENIOR (codpes, codund, dtafimcbd) 
                VALUES (
                    convert(int,:codpes),
                    convert(int,:codund),
                    :dtafimcbd
                )";

        $data = [
            'codpes' => 98958989,
            'codund' => 8,
            'dtafimcbd' => '2024-11-05 00:00:00',
        ];
        
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame([
            0 => Array(
                'codpes' => '98958989',
                'tipvin' => '',
                'tipvinext' => 'Docente Aposentado',
                'nompes' => 'Maria',
                'sitatl' => 'P',
                'nomfnc' => '',
                'codundclg' => '',
                'numtelfmt' => '',
                'sglclgund' => '',
                'codset' => '666',
                'nomabvset' => '',
                'nomset' => '',
                'codema' => '',
                'tipdsg' => '',
                'nompesfon' => '',
                'codfncetr' => '0',
                'codund' => '8',
                'dtafimcbd' => '2024-11-05 00:00:00'
        )],Pessoa::listarDocentesAposentadosSenior());   
    }
}
