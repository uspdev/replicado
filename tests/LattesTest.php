<?php

namespace Uspdev\Replicado\Tests;

use PHPUnit\Framework\TestCase;
use Uspdev\Replicado\Lattes;
use Uspdev\Replicado\DB;

class LattesTest extends TestCase
{
    public function test_id(){
        DB::getInstance()->prepare('DELETE FROM DIM_PESSOA_XMLUSP')->execute();

        $sql = "INSERT INTO DIM_PESSOA_XMLUSP (codpes, idfpescpq) VALUES 
                                   (convert(int,:codpes), :idfpescpq)";
        $data = [
            'codpes' => 123456,
            'idfpescpq' => '1234567890'
        ];
        DB::getInstance()->prepare($sql)->execute($data);
        $this->assertSame('1234567890',Lattes::id(123456));
    }
}