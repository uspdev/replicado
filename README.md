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

    SELECT nompes,... FROM pessoa WHERE codpes='123'

Usa-se uma classe PHP que faz a abstração do acesso e portanto deixa 
seu código muito mais limpo, além de torna as consultas reutilizáveis:

    Pessoa::nomeCompleto('123');

## Dependências

* É necessário pelo menos o PHP v7.3.
* Esta biblioteca precisa da extensão `ext-sybase`. No ubuntu instale com `sudo apt install php-sybase`
* Esta biblioteca usa opcionalmente `uspdev/cache`. Caso queira usar o cache consulte a documentação em: https://github.com/uspdev/cache

## Como usar

Instale via composer

    composer require uspdev/replicado

Exemplo de uso

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
    putenv('REPLICADO_CODCUR=1,2,3');


    # Opcionais
    putenv('REPLICADO_PATHLOG=path/to/your.log');
    putenv('REPLICADO_SYBASE=0');
    putenv('REPLICADO_USAR_CACHE=0');

    $emails = Pessoa::emails('123456');
    print_r($emails);
```

### Explicações das variáveis

A maioria das variáveis são autoexplicativas mas outras não.

*REPLICADO_CODUNDCLG* -essa variável pode conter múltiplos valores pois representa a unidade ou o colegiado:

    REPLICADO_CODUNDCLG=8,27

Atenção, NÃO usar aspas, como no neste exemplo: *REPLICADO_CODUNDCLG="8,27"*.

REPLICADO_CODCUR - ??

REPLICADO_SYBASE - serve para indicar se vc está usando SYBASE ou MSSQL. Implica:
* na conversão para UTF-8 pela biblioteca ou pelo freetds
* na remoção de espaços adicionais no final das strings

Dependendo da configuração do MSSQL pode ser necessário ativar essa variável.

REPLICADO_USAR_CACHE - o replicado pode usar memcached através da biblioteca (https://github.com/uspdev/cache). 

Para usar é necessário instalar ele com 

    composer require uspdev/Cache

e seguir a documentação da biblioteca para levantar o servidor memcached e configurar ele. 

Por fim ative o cache do replicado com do

    putenv('REPLICADO_USAR_CACHE=1');

Em produção vale a pena usar mas em testes mantenha desativado.

## Informações sobre tabelas

   [https://uspdigital.usp.br/replunidade](https://uspdigital.usp.br/replunidade)

## Recomendações para contribuir com este projeto

Vídeo:  [https://youtu.be/p5dFJOrMN30](https://youtu.be/p5dFJOrMN30)

O replicado pode consultar tanto o MSSQL quanto o sybase-ase e em diversas versões diferentes. Dessa forma é necessário manter a compatibilidade com os diversos replicados das unidades.

* Abra uma issue antes de começar a mexer no código. A discussão prévia é importante para alinhar as idéias.
* As contribuições serão aceitas por meio de pull requests. Para tanto faça as alterações em uma branch issue_xx.
* Ao criar um novo método, lembre de documentar o DOCBLOCK
* Ao criar um novo método, crie o teste correspondente
* A branch master é considerada estável e pode ser usada em produção, porém os releases têm sido regulares.
* Os argumentos dos métodos devem ser tipados, incluindo int, string etc
* Deve-se dar preferência para aspas simples em strings pois o PHP não tenta parsear seu conteúdo

Sugestão para nomear métodos:

* listarXxx - retorna lista de arrays de Xxx
* obterXxxx - retorna somente um registro (array) de Xxxx
* contarXxxx - retorna uma contagem (count()) de registros de Xxxx
* retornarXxxx - retorna um valor do registro (string, int, etc)
* verificarXxxx - retorna true ou false em função da condição Xxxx

Docblock

Coloque o campo @author no docblock do método. Assim facilita consultar o autor sobre o método.
### phpunit

Ao criar um método novo é necessário criar um método correspondente de teste, usando o phpunit. Para isso, você precisa de um banco de dados sybase ou mssql 
que possa deletar as tabelas, há quatro opções para você rodar os testes:

- Baixar o mssql ou sybase e instalá-los manualmente
- Subir um container Sybase com https://github.com/uspdev/asedocker
- Instalar o sybase com ansible https://github.com/fflch/ansible-role-sap-ase
- Nós mantemos um servidor sybase de testes, se quiser, solicite as credenciais uspdev@usp.br

As Pull Request são automaticamente testadas pelo travis, assim, antes de abrir um
PR garanta que os testes estão passando:

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
