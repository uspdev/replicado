## Replicado

Biblioteca PHP que abstrai o acesso aos dados do replicado USP, 
isto é, ao invés de inserir uma consulta SQL diretamente em seu código, 
como por exemplo: 

    SELECT codpes,nompes,... FROM pessoa WHERE codpes='123'

Usa-se uma classe PHP que faz a abstração do acesso e portanto deixa 
seu código muito mais limpo e torna as consultas reutilizáveis:

    $pessoa->dump('123')

O projeto tem dois repositório, um *open source* com as classes PHP 
[replicado](https://github.com/uspdev/replicado) e outro de acesso interno 
no gitlab da USP, este somente com as *queries* [replicado_queries](https://git.uspdigital.usp.br/uspdev/replicado_queries).
Os arquivos SQL estão separados por dois motivos:

 1. Não expor publicamente a estrutura interna dos dados das tabelas do replicado
 2. Permitir que as consultas sejam reutilizadas em projetos com outras linguagens que não PHP

## Adicione essa lib em seu projeto PHP:

    composer config repositories.replicado git https://github.com/uspdev/replicado.git
    composer require uspdev/replicado:dev-master

## Baixe as consultas (TODO: incluir essa task no composer):

    cd vendor/uspdev/replicado/src
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
    $pessoa = new Pessoa($conn->db);
    print_r($pessoa->dump('123456'));

## Informações sobre tabelas:

   [https://uspdigital.usp.br/replunidade](https://uspdigital.usp.br/replunidade)

## Métodos existentes:

### Classe Pessoa 

 - *dump($codpes)*: recebe codpes e retorna todos campos da tabela Pessoa para o codpes em questão
 - *cracha($codpes)*: recebe codpes e retorna todos campos da tabela catr_cracha para o codpes em questão 
 - *email($codpes)*: recebe codpes e retorna email de correspondência da pessoa
 - *emails($codpes)*: recebe codpes e retorna todos emails da pessoa
 - *emailusp($codpes)*: recebe codpes e retorna email usp da pessoa
 
### Classe Posgraduacao

 - *verificaSeAtivo($codpes,$unidade)*: verifica se aluno (codpes) tem matrícula ativa na pós-graduação da unidade
