## Replicado

API (ou PHP library) que abstrai o acesso aos dados do replicado USP, 
isto é, ao invés de inserir uma consulta SQL diretamente em seu código, 
como por exemplo: 

    SELECT nompes FROM pessoa WHERE codpes='123'

Usa-se uma classe PHP que faz a abstração do acesso e portanto deixa 
seu código muito mais limpo e torna as consultas reutilizáveis: 

    $db->pessoa('123')

O projeto tem dois repositório, um *open source* com as classes PHP 
[replicado](https://github.com/uspdev/replicado) e outro de acesso interno 
no gitlab da USP, este somente com as *queries* [replicado_queries](https://git.uspdigital.usp.br/uspdev/replicado_queries).
Os arquivos SQL estão separados por dois motivos:

 1. Não expor a estrutura dos dados das tabelas do replicado
 2. Permitir que as consultas sejam reutilizadas em projetos com outras linguagens que não PHP

## Adicione essa lib em seu projeto PHP:

    composer config repositories.replicado git https://github.com/uspdev/replicado.git
    composer require uspdev/replicado:dev-master

## Baixe as consultas (TODO: incluir essa task no composer):

    cd vendor/uspdev/replicado
    git clone git@git.uspdigital.usp.br:uspdev/replicado_queries

## Biblioteca PHP usada para conexão (testado com PHP7.2):

    php-sybase

## Para testar:

    <?php
    namespace Meu\Lindo\App;
    require_once __DIR__ . '/vendor/autoload.php';

    use Uspdev\Replicado\Connection;
    use Uspdev\Replicado\Pessoa;

    $conn = new Connection($ip,$port,$db,$user,$pass);
    $p = new Pessoa($conn->db);
    print_r($p->pessoa('123456'));

## Informações sobre tabelas:

    https://uspdigital.usp.br/replunidade

## Métodos existentes:

 - Pessoa::pessoa($codpes): recebe codpes e retorna todos campos da tabela Pessoa

