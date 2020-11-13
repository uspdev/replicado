<?php

namespace Uspdev\Replicado\Tests;

use PHPUnit\Framework\TestCase;
use Uspdev\Replicado\Estrutura;
use Uspdev\Replicado\DB;

class EstruturaTest extends TestCase
{
    public function test_listarSetores(){
        # Limpando Tabela
        DB::getInstance()->prepare('DELETE FROM SETOR')->execute();
        
        $codund = getenv('TEST_SYBASE_CODUNDCLG');
        
        $sql = "INSERT INTO SETOR (codset, tipset, nomabvset, nomset, codsetspe, codund) VALUES 
                (convert(int,:codset), :tipset, :nomabvset, :nomset, convert(int,:codsetspe), convert(int, $codund))";

        $datas = [
            [
                'codset' => '1',
                'tipset' => 'Unidade',
                'nomabvset' => 'UND',
                'nomset' => 'Diretoria da Unidade',
                'codsetspe' => '0'
            ],
            [
                'codset' => '2',
                'tipset' => 'Departamento de Ensino',
                'nomabvset' => 'DPTO1',
                'nomset' => 'Departamento1',                
                'codsetspe' => '1'
            ],
            [
                'codset' => '3',
                'tipset' => 'Departamento de Ensino',
                'nomabvset' => 'DPTO2',
                'nomset' => 'Departamento2',
                'codsetspe' => '1'
            ],
            [
                'codset' => '4',
                'tipset' => 'Seção Técnica',
                'nomabvset' => 'STI',
                'nomset' => 'Seção Técnica de Informática',
                'codsetspe' => '1'                
            ],
            [
                'codset' => '5',
                'tipset' => 'Seção Técnica',
                'nomabvset' => 'SI-DPTO1',
                'nomset' => 'Seção de Informática',
                'codsetspe' => '2'                
            ]
        ];

        foreach ($datas as $data) {
            DB::getInstance()->prepare($sql)->execute($data);
        }
        
        $this->assertSame($datas,Estrutura::listarSetores());
    }

    public function test_getChefiaSetor()
    {
        # Limpando Tabela
        DB::getInstance()->prepare('DELETE FROM SETOR')->execute();
        DB::getInstance()->prepare('DELETE FROM LOCALIZAPESSOA')->execute();

        $codund = getenv('TEST_SYBASE_CODUNDCLG');

        $sql_setor = "INSERT INTO SETOR (codset, tipset, nomabvset, nomset, codsetspe, codund) VALUES 
                (convert(int,:codset), :tipset, :nomabvset, :nomset, convert(int,:codsetspe), convert(int, $codund))";

        $data_setores = [
            [
                'codset' => '1',
                'tipset' => 'Unidade',
                'nomabvset' => 'UND',
                'nomset' => 'Diretoria da Unidade',
                'codsetspe' => '0'
            ],
            [
                'codset' => '2',
                'tipset' => 'Departamento de Ensino',
                'nomabvset' => 'DPTO1',
                'nomset' => 'Departamento1',                
                'codsetspe' => '1'
            ],
            [
                'codset' => '3',
                'tipset' => 'Departamento de Ensino',
                'nomabvset' => 'DPTO2',
                'nomset' => 'Departamento2',
                'codsetspe' => '1'
            ],
            [
                'codset' => '4',
                'tipset' => 'Seção Técnica',
                'nomabvset' => 'STI',
                'nomset' => 'Seção Técnica de Informática',
                'codsetspe' => '1'                
            ],
            [
                'codset' => '5',
                'tipset' => 'Seção Técnica',
                'nomabvset' => 'SI-DPTO1',
                'nomset' => 'Seção de Informática',
                'codsetspe' => '2'                
            ]
        ];

        foreach ($data_setores as $data_setor) {
            DB::getInstance()->prepare($sql_setor)->execute($data_setor);
        }

        $sql_pessoa = "INSERT INTO LOCALIZAPESSOA (codpes, nompes, nomfnc, codset, tipvinext, tipdsg, codfncetr) VALUES 
                (convert(int,:codpes), :nompes, :nomfnc, convert(int,:codset), :tipvinext, :tipdsg, convert(int,:codfncetr))";

        $data_pessoas = [
            [
                'codpes' => 1234,
                'nompes' => 'Fulano de Tal',
                'nomfnc' => 'Diretor',
                'codset' => 1,
                'tipvinext' => 'Servidor Designado',
                'tipdsg' => 'D',
                'codfncetr' => 0
            ],
            [
                'codpes' => 56789,
                'nompes' => 'Beltrano de Tal',
                'nomfnc' => 'Chefe Adm Serviço',
                'codset' => 2,
                'tipvinext' => 'Servidor Designado',
                'tipdsg' => 'D',
                'codfncetr' => 0
            ],
            [
                'codpes' => 101112,
                'nompes' => 'Fulano da Silva',
                'nomfnc' => 'Chefe de Seção',
                'codset' => 2,
                'tipvinext' => 'Servidor Designado',
                'tipdsg' => 'S',
                'codfncetr' => 0
            ],
            [
                'codpes' => 131415,
                'nompes' => 'Maria Mariana',
                'nomfnc' => 'Chefe',
                'codset' => 4,
                'tipvinext' => 'Servidor Designado',
                'tipdsg' => 'D',
                'codfncetr' => 0
            ],
            [
                'codpes' => 161718,
                'nompes' => 'João de Tal',
                'nomfnc' => 'Vice Chefe',
                'codset' => 1,
                'tipvinext' => 'Servidor Designado',
                'tipdsg' => 'D',
                'codfncetr' => 0
            ]
        ];

        foreach ($data_pessoas as $data_pessoa) {
            DB::getInstance()->prepare($sql_pessoa)->execute($data_pessoa);
        }

        $teste = [
            [
                [
                    'codpes' => '161718',
                    'nompes' => 'João de Tal',
                    'nomfnc' => 'Vice Chefe',
                    'codsetspe' => '0',
                    'nomabvset' => 'UND',
                    'nomset' => 'Diretoria da Unidade',
                ],
                [
                    'codpes' => '1234',
                    'nompes' => 'Fulano de Tal',
                    'nomfnc' => 'Diretor',
                    'codsetspe' => '0',
                    'nomabvset' => 'UND',
                    'nomset' => 'Diretoria da Unidade',
                ]
            ],
            [
                [
                    'codpes' => '56789',
                    'nompes' => 'Beltrano de Tal',
                    'nomfnc' => 'Chefe Adm Serviço',
                    'codsetspe' => '1',
                    'nomabvset' => 'DPTO1',
                    'nomset' => 'Departamento1',
                ],
                [
                    'codpes' => '101112',
                    'nompes' => 'Fulano da Silva',
                    'nomfnc' => 'Chefe de Seção',
                    'codsetspe' => '1',
                    'nomabvset' => 'DPTO1',
                    'nomset' => 'Departamento1',
                ]
            ],
            [
                [
                    'codpes' => '56789',
                    'nompes' => 'Beltrano de Tal',
                    'nomfnc' => 'Chefe Adm Serviço',
                    'codsetspe' => '1',
                    'nomabvset' => 'DPTO1',
                    'nomset' => 'Departamento1',
                ]
            ]
        ];

        $this->assertSame($teste[0],Estrutura::getChefiaSetor(1));
        $this->assertSame($teste[1],Estrutura::getChefiaSetor(2));
        $this->assertSame($teste[2],Estrutura::getChefiaSetor(2,false));
    }    
}