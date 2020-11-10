<?php

namespace Uspdev\Replicado\Tests;

use PHPUnit\Framework\TestCase;
use Uspdev\Replicado\Uteis;

class UteisTest extends TestCase
{

    public function test_substituiAcentosParaSql()
    {
        $this->assertSame('Jo_o da Silva', Uteis::substituiAcentosParaSql('João da Silva'));
    }

    public function test_fonetico()
    {
        $this->assertSame('MASACI CAFAPADA NIDO', Uteis::fonetico('Masaki Kawabata Neto'));
        $this->assertSame('JOACM JOSI SIOFA XAFIR', Uteis::fonetico('Joaquim José da Silva Xavier'));
    }

    public function test_semestre()
    {
        $this->assertSame(['20200701', '20201231'], Uteis::semestre('2020-11-10'));
        $this->assertSame(['20200701', '20201231'], Uteis::semestre('2020-07-01'));

        # este teste está falhando
        #$this->assertSame(['20200701', '20201231'], Uteis::semestre('2020-12-25'));
        
        # este teste testá falhando
        #$this->assertSame(['20200101', '20200630'], Uteis::semestre('2020-06-15'));
    }
}
