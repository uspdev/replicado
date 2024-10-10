[![Build Status](https://travis-ci.org/uspdev/replicado.svg?branch=master)](https://travis-ci.org/uspdev/replicado)

[![Latest Stable Version](https://poser.pugx.org/uspdev/replicado/v/stable.svg)](https://packagist.org/packages/uspdev/replicado)
[![Total Downloads](https://poser.pugx.org/uspdev/replicado/downloads.svg)](https://packagist.org/packages/uspdev/replicado)

![GitHub pull requests](https://img.shields.io/github/issues-pr-raw/uspdev/replicado.svg) 
![GitHub closed pull requests](https://img.shields.io/github/issues-pr-closed-raw/uspdev/replicado.svg)

![GitHub issues](https://img.shields.io/github/issues/uspdev/replicado.svg) 
![GitHub closed issues](https://img.shields.io/github/issues-closed/uspdev/replicado.svg)

## Replicado

Biblioteca PHP que abstrai em classes a camada de acesso ao replicado USP, 
isto é, ao invés de inserir uma consulta SQL diretamente em seu código, 
como por exemplo: 

    SELECT nompes,... FROM pessoa WHERE codpes='123'

Usa-se uma classe PHP que faz a abstração do acesso e portanto deixa 
seu código muito mais limpo, além de torna as consultas reutilizáveis:

    Pessoa::nomeCompleto('123');

## Dependências

* É necessário pelo menos o PHP v7.3 e é compatível com php v8.0 e posteriores
* Esta biblioteca precisa da extensão `ext-sybase`. No ubuntu instale com `sudo apt install php-sybase`
* Esta biblioteca usa opcionalmente `uspdev/cache`. Caso queira usar o cache consulte a documentação em: https://github.com/uspdev/cache

## Como usar

Instale via composer

    composer require uspdev/replicado

Exemplo de uso passando `$config`

```php
<?php
namespace Meu\Lindo\App;
require_once __DIR__ . '/vendor/autoload.php';
use Uspdev\Replicado\Pessoa;
use Uspdev\Replicado\Replicado;

$config = [
    'host' => '192.168.100.89',
    'port' => 1498,
    'database' => 'rep_dbc',
    'username' => 'dbmaint_read',
    'password' => 'secret',
    'codundclg' => '8',
    'codundclgs' => '8,84',
    'pathlog' => 'path/to/your.log',
    'sybase' => true,
    'usarCache' => false,
    'debug' => false,
    'debugLevel' => 1,
];
Replicado::setConfig($config);

$emails = Pessoa::emails('123456');
print_r($emails);
```

Exemplo de uso com variáveis de ambiente

```php
<?php
namespace Meu\Lindo\App;
require_once __DIR__ . '/vendor/autoload.php';
use Uspdev\Replicado\Pessoa;

# Obrigatórias
putenv('REPLICADO_HOST=192.168.100.89');
putenv('REPLICADO_PORT=1498');
putenv('REPLICADO_DATABASE=rep_dbc');
putenv('REPLICADO_USERNAME=dbmaint_read');
putenv('REPLICADO_PASSWORD=secret');
putenv('REPLICADO_CODUNDCLG=8');
putenv('REPLICADO_CODUNDCLGS=8,84');

# Opcionais - estes são os valores default
putenv('REPLICADO_PATHLOG=/tmp/replicado.log');
putenv('REPLICADO_SYBASE=true');
putenv('REPLICADO_USAR_CACHE=false');
putenv('REPLICADO_DEBUG=false');
putenv('REPLICADO_DEBUG_LEVEL=1');

$emails = Pessoa::emails('123456');
print_r($emails);
```

### Como usar no laravel

Veja o projeto [Uspdev\\laravel-replicado](https://github.com/uspdev/laravel-replicado).

### Explicações das variáveis

A maioria das variáveis são autoexplicativas mas outras não.

**REPLICADO_CODUNDCLG** - essa variável é o código da unidade. Até 11/2022, ela podia conter valores separados por vírgula. No entanto, para manter compatibilidade e organizar melhor, criou-se outra váriável para conter múltiplos valores:

    REPLICADO_CODUNDCLG=8

**REPLICADO_CODUNDCLGS** (com S no final) - Representa os colegiados da unidade. Importante para as unidades que tem cursos compartilhados.

    REPLICADO_CODUNDCLGS=8,27

Atenção, NÃO usar aspas, como neste exemplo: *REPLICADO_CODUNDCLG="8,27"*.

**REPLICADO_SYBASE** - serve para indicar se vc está usando SYBASE ou MSSQL. Implica:
* na conversão para UTF-8 pela biblioteca ou pelo freetds
* na remoção de espaços adicionais no final das strings

Dependendo da configuração do MSSQL pode ser necessário ativar essa variável.

**REPLICADO_USAR_CACHE** - o replicado pode usar memcached através da biblioteca (https://github.com/uspdev/cache).

Para usar é necessário instalar ele com 

    composer require uspdev/Cache

e seguir a documentação da biblioteca para levantar o servidor memcached e configurar ele. 

Por fim ative o cache do replicado com

    putenv('REPLICADO_USAR_CACHE=1');

Ainda é possível controlar o comportamento do cache somente para o replicado com

    putenv('REPLICADO_CACHE_EXPIRY=14400'); // 4 horas para expirar
    putenv('REPLICADO_CACHE_SMALL=32'); // tamanho máx em bytes do retorno que não vai ser cacheado

Em produção vale a pena usar mas em testes mantenha desativado.

## Debug

A variável debug, se `true`, mostra mensagens na tela em caso de erro.

Por padrão os erros também são gravados no log. Caso queira gravar em log as queries executadas no BD do replicado, 
aumente o debugLevel para 2.

    putenv('REPLICADO_DEBUG_LEVEL=2');

## Config reset

No método `Lattes::obterZip()`, pode ser necessário alterar a configuração do replicado momentaneamente definindo `sybase = false`. Isso é possível com o comando abaixo

        Replicado::setConfig(['sybase' => false]);

Para retornar às configurações do env, pode-se utilizar o comando reset como segue

        Replicado::setConfig(['reset' => true]);


## Informações sobre tabelas

   [https://uspdigital.usp.br/replunidade](https://uspdigital.usp.br/replunidade)

## Recomendações para contribuir com este projeto

Vídeo:  [https://youtu.be/p5dFJOrMN30](https://youtu.be/p5dFJOrMN30)

O replicado pode consultar tanto o MSSQL quanto o sybase-ase e em diversas versões diferentes. Dessa forma é necessário manter a compatibilidade com os diversos replicados das unidades.

* Abra uma issue antes de começar a mexer no código. A discussão prévia é importante para alinhar as idéias.
* As contribuições serão aceitas por meio de pull requests. Para tanto faça as alterações em uma branch issue_xx.
* Documentar o DOCBLOCK
    * Texto principal, texto complementar, @param, @return
    * @author seu-nome, em xx/xx/xxxx ou
    * @author seu-nome, modificado em xx/xx/xxxx
* Coloque o sql em resources/queries
* Os argumentos dos métodos devem ser tipados, incluindo int, string etc
* (11/2022) Caso ocorra algum erro, os métodos `DB::fetch` e `DB::fetchAll` retornam `false` e uma mensagem de erro. Em caso de retorno "vazio", alguns métodos precisam de tratamento:
    * **obterXxxx**: `PDOStatement::fetch()` retorna false em caso de vazio. Nesse caso use\
        `return DB::fetch($query) :? [];`
    * **retornarXxxx**: deve retornar `null`
* Dar preferência para aspas simples em strings pois o PHP não tenta parsear seu conteúdo
* A branch `master` é considerada estável e pode ser usada em produção, porém os `releases` têm sido regulares.

Referência: `Pessoa::listarDesignados()`

Sugestão para nomear métodos:

1. **listarXxx** - retorna lista de registros de dados (`fetchAll`)
2. **obterXxxx** - retorna somente um registro (`fetch`)
3. **contarXxxx** - retorna uma contagem (`count()`) - retorno tipo `int`
4. **retornarXxxx** - retorna um valor do registro - retorno `string`, `int`, etc
5. **verificarXxxx** - retorna true ou false em função da condição - retorno `bool`

OBS1.: Quando passar parâmetro `array` simples, deixar opcional passar `string` separada por vírgula. Ex.: `Pessoa::contarServidoresSetor()`

OBS2.: (11/2022) As queries dos métodos devem ficar em `resources/queries` e as substituições, se necessário podem ser feitas no método `DB::getQuery('arquivo.sql', $replaces)`

OBS3.: (10/2023) Se necessário usar `REPLICADO_CODUNDCLGS` ou `REPLICADO_CODUNDCLG` na query, basta colocar `__codundclgs__` ou `__codundclg__` que a biblioteca fará a substituição correspondente. A substituição é feita no método `DB::overrideFetch`. Mas se quiser passar algo diferente do `config`, fique à vontade.

OBS4.: Nos métodos não usar `getenv('REPLICADO_VARIAVEL')`. Usar, se necessário, `Replicado::getConfig('variavel')`.

### Métodos deprecados

Se você utiliza um desses métodos nos seus sistemas, atualize para o novo método correspondente.

2020
- Pessoa::nome -> procurarPorNome (11/2020)
- Pessoa::nomeFonetico -> procurarPorNome (11/2020)

2021
- Pessoa::vinculosSiglas -> obterSiglasVinculosAtivos (3/2021)
- Pessoa::setoresSiglas -> obterSiglasSetoresAtivos (6/2021)
- Pessoa::emailusp -> retornarEmailUsp (6/2021)
- Pessoa::designados -> listarDesignados (7/2021)
- Graduacao::ativos -> listarAtivos (10/2021)
- Pessoa::nomeCompleto -> obterNome (12/2021)

2022
- Pessoa::servidores -> listarServidores (1/2022)
- Pessoa::vinculosSetores -> listarVinculosSetores (9/2022)
- Pessoa::tiposVinculos -> listarTiposVinculoExtenso (11/2022)
- Graduacao::curso -> obterCursoAtivo (11/2022)
- Pessoa::listarVinculosSetores -> a ser removido e adicionado no uspdev/web-ldap-admin listarVinculosExtensoSetores (11/2022)


### phpunit

De forma opcional, se quiser fazer do jeito *bonitinho*, pode-se criar teste unitário, usando o phpunit. 
Para isso, você precisa de um banco de dados sybase ou mssql que possa deletar as tabelas, 
há quatro opções para você rodar os testes:

- Baixar o mssql ou sybase e instalá-los manualmente
- Subir um container Sybase com https://github.com/uspdev/asedocker
- Instalar o sybase com ansible https://github.com/fflch/ansible-role-sap-ase
- Nós mantemos um servidor sybase de testes, se quiser, solicite as credenciais uspdev@usp.br

Os Pull Request são automaticamente testadas pelo travis (desativado por enquanto).
Para verificar se os testes estão passando:

    ./vendor/bin/phpunit

## Documentação

Consulte a documentação em:
[https://uspdev.github.io/replicado/](https://uspdev.github.io/replicado/)

A documentação é auto-gerada usando [phpDocumentor](https://www.phpdoc.org/) e não é necessário
fazer nada, pois a cada push uma github action a atualiza.

Mas se quiser testá-la localmente:

    wget http://phpdoc.org/phpDocumentor.phar
    sudo mv phpDocumentor.phar /usr/local/bin/phpdoc
    sudo chmod a+x /usr/local/bin/phpdoc

Ainda é necessário instalar:

    sudo apt install graphviz

Gerando a documentação:

    phpdoc
