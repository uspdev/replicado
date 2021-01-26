<?php

namespace Uspdev\Replicado\Tests;

use PHPUnit\Framework\TestCase;
use Uspdev\Replicado\DB;
use Uspdev\Replicado\Uteis;
use Dotenv\Dotenv;
use Uspdev\Replicado\Pessoa;

/** Build faker data after run all phpunit tests
 */
class BuildFakerDataTest extends TestCase
{
    /**
     * Subir todos dados testado até aqui para deixar um
     * banco de testes pronto para uso
     */
    public function test_deploy_data()
    {
        # Deploy all faker data
        $files = scandir(__DIR__ . '/data/');
        $files = array_diff( $files, ['.','..'] );
        foreach($files as $file){
            $sql = file_get_contents(__DIR__ . '/data/' . $file);
            DB::getInstance()->exec($sql);
        }

        # Uma única asserção para o phpunit não ficar reclamando...
        $this->assertSame('Fulano da Silva',Pessoa::nomeCompleto(123456));
    }
}
