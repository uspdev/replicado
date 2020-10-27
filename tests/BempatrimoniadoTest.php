<?php

namespace Uspdev\Replicado\Tests;

use PHPUnit\Framework\TestCase;
use Uspdev\Replicado\Bempatrimoniado;
use Uspdev\Replicado\DB;

class BempatrimoniadoTest extends TestCase {

    public function test_dump(){
        
        DB::getInstance()->prepare('DELETE FROM BEMPATRIMONIADO')->execute();

        $sql = "INSERT INTO BEMPATRIMONIADO (numpat) VALUES 
                                   (convert(int,:numpat))";
        $data = [
            'numpat' => 123456
        ];
        DB::getInstance()->prepare($sql)->execute($data);

        $this->assertSame('123456',Bempatrimoniado::dump(123456)['numpat']);
    }
}
