<?php

namespace Uspdev\Replicado\Tests;

use PHPUnit\Framework\TestCase;
use Uspdev\Replicado\Pessoa;

class PessoaTest extends TestCase
{
    public function test_nomeCompleto(){
        $this->assertSame('Fulano da Silva',Pessoa::nomeCompleto(123456));
    }
}