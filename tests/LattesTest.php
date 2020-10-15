<?php

namespace Uspdev\Replicado\Tests;

use PHPUnit\Framework\TestCase;
use Uspdev\Replicado\Lattes;
use Uspdev\Replicado\DB;

class LattesTest extends TestCase
{
    public function test_idLattes(){
        DB::getInstance()->prepare('DELETE FROM DIM_PESSOA_XMLUSP')->execute();

        $sql = "INSERT INTO DIM_PESSOA_XMLUSP (codpes, idfpescpq) VALUES 
                                   (convert(int,:codpes),convert(int,:idfpescpq))";
        $data = [
            'codpes' => 123456,
            'idfpescpq' => 658585
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $this->assertSame('658585',Lattes::idLattes(123456)['idLattes']);
    }
}