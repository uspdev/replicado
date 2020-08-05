[![Build Status](https://travis-ci.org/uspdev/replicado.svg?branch=master)](https://travis-ci.org/uspdev/replicado)
[![Latest Stable Version](https://poser.pugx.org/uspdev/replicado/v/stable.svg)](https://packagist.org/packages/uspdev/replicado)
[![Total Downloads](https://poser.pugx.org/uspdev/replicado/downloads.svg)](https://packagist.org/packages/uspdev/replicado)

![GitHub pull requests](https://img.shields.io/github/issues-pr-raw/uspdev/replicado.svg) 
![GitHub closed pull requests](https://img.shields.io/github/issues-pr-closed-raw/uspdev/replicado.svg)

![GitHub issues](https://img.shields.io/github/issues/uspdev/replicado.svg) 
![GitHub closed issues](https://img.shields.io/github/issues-closed/uspdev/replicado.svg)

## Replicado

Biblioteca PHP que abstrai o acesso aos dados do replicado USP, 
isto é, ao invés de inserir uma consulta SQL diretamente em seu código, 
como por exemplo: 

    SELECT codpes,nompes,... FROM pessoa WHERE codpes='123'

Usa-se uma classe PHP que faz a abstração do acesso e portanto deixa 
seu código muito mais limpo e torna as consultas reutilizáveis:

    Pessoa::dump('123');

## Dependências

* É necessário pelo menos o PHP v7.0.
* Esta biblioteca precisa da extensão `ext-sybase`. No ubuntu instale com `sudo apt install php-sybase`
* monolog

## Como usar

Instale via composer

    composer require uspdev/replicado

Exemplo de uso

```php
    <?php
    namespace Meu\Lindo\App;
    require_once __DIR__ . '/vendor/autoload.php';
    use Uspdev\Replicado\Pessoa;
    
    putenv('REPLICADO_HOST=192.168.100.89');
    putenv('REPLICADO_PORT=1498');
    putenv('REPLICADO_DATABASE=rep_dbc');
    putenv('REPLICADO_USERNAME=dbmaint_read');
    putenv('REPLICADO_PASSWORD=secret');
    putenv('REPLICADO_PATHLOG=path/to/your.log');
    putenv('REPLICADO_CODUNDCLG=8');

    $emails = Pessoa::emails('123456');
    print_r($emails);
```

A variável *REPLICADO_CODUNDCLG* podem contem múltiplas unidades:

    REPLICADO_CODUNDCLG=8,27

Atenção, NÃO usar aspas: *REPLICADO_CODUNDCLG="8,27"*.

## Para testar

Rode na linha de comando

    php test/run.php credentials.php

Se preferir crie e rode alguns exemplos.


O codundclg, na graduação, corresponde a um colegiado e uma unidade pode conter mais de um. Nesse caso, coloque em uma lista separada por vírgulas: `putenv('REPLICADO_CODUNDCLG=8,90');`

## Informações sobre tabelas

   [https://uspdigital.usp.br/replunidade](https://uspdigital.usp.br/replunidade)


## Contribuindo com este projeto

Veja o arquivo [contrib.md](doc/contrib.md) com orientações de como contribuir.

## Métodos existentes

Classes

* [pessoa](doc/metodos_pessoa.md)
* [graduacao](doc/metodos_graduacao.md)
* [posgraduacao](doc/metodos_posgraduacao.md)
* [bempatrimoniado](doc/metodos_bempatrimoniado.md)
* [lattes](doc/metodos_lattes.md)
