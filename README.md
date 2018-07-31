## Replicado

Biblioteca PHP que abstrai o acesso aos dados do replicado USP, 
isto é, ao invés de inserir uma consulta SQL diretamente em seu código, 
como por exemplo: 

    SELECT codpes,nompes,... FROM pessoa WHERE codpes='123'

Usa-se uma classe PHP que faz a abstração do acesso e portanto deixa 
seu código muito mais limpo e torna as consultas reutilizáveis:

    Pessoa::dump('123')

O projeto tem dois repositório, um *open source* com as classes PHP 
([replicado](https://github.com/uspdev/replicado)) e outro de acesso interno 
no gitlab da USP, este somente com as *queries* ([replicado_queries](https://git.uspdigital.usp.br/uspdev/replicado_queries)).
Os arquivos SQL estão separados por dois motivos:

 1. Não expor publicamente a estrutura interna dos dados das tabelas do replicado
 2. Permitir que as consultas sejam reutilizadas em projetos com outras linguagens que não PHP

## Adicione essa lib em seu projeto PHP:

    composer require uspdev/replicado

## Baixe as consultas (TODO: incluir essa task no composer):

    git clone git@git.uspdigital.usp.br:uspdev/replicado_queries vendor/uspdev/replicado/src/replicado_queries

## Biblioteca PHP usada para conexão (testado com PHP7.2):

    php-sybase

## Para testar:

    <?php
    namespace Meu\Lindo\App;
    require_once __DIR__ . '/vendor/autoload.php';
    use Uspdev\Replicado\Pessoa;
    
    putenv('REPLICADO_HOST=192.168.100.89');
    putenv('REPLICADO_PORT=1498');
    putenv('REPLICADO_DATABASE=rep_dbc');
    putenv('REPLICADO_USERNAME=dbmaint_read');
    putenv('REPLICADO_PASSWORD=secret');

    $emails = Pessoa::emails('123456');
    print_r($emails);

## Informações sobre tabelas:

   [https://uspdigital.usp.br/replunidade](https://uspdigital.usp.br/replunidade)

## Métodos existentes:

### Classe Pessoa 

 - *dump($codpes)*: recebe codpes e retorna todos campos da tabela Pessoa para o codpes em questão
 - *nome($nome)*: recebe uma string nome e retorna os resultados para a tabela Pessoa
 - *cracha($codpes)*: recebe codpes e retorna todos campos da tabela catr_cracha para o codpes em questão 
 - *email($codpes)*: recebe codpes e retorna email de correspondência da pessoa
 - *emails($codpes)*: recebe codpes e retorna todos emails da pessoa
 - *emailusp($codpes)*: recebe codpes e retorna email usp da pessoa
 - *localiza($codpes)*: retorna vínculos ativos da pessoa
 - *docentesAtivos($unidade)*: retorna *array* de todos os docentes ativos na unidade
 
### Classe Graduacao

 - *verifica($codpes,$unidade)*: verifica se aluno (codpes) tem matrícula ativa na graduação da unidade
 - *ativos($unidade, $strFiltro = '')*: retorna *array* de todos alunos de graduação ativos na unidade ou somente os alunos de graduação ativos que obedeçam o filtro
 - *ativosCsv($unidade)*: retorna *csv* de todos alunos de graduação ativos na unidade
 - *curso($codpes,$unidade)*: retorna os dados acadêmicos do aluno de graduação ativo na unidade
 - *nomeCurso($codcur)*: retorna o nome do curso 
 - *nomeHabilitacao($codhab, $codcur)*: retorna o nome da habilitação
 - *obterCursosHabilitacoes($unidade)*: retorna os cursos e habilitações na unidade
 - *obterDisciplinas($arrCoddis)*: recebe um *array* com o prefixo dos codigos das disciplinas e retorna *array* com todas as disciplinas na unidade
 - *nomeDisciplina($coddis)*: recebe o código da disciplina *string* e retorna *string* com o nome da disciplina
 
### Classe Posgraduacao

 - *verifica($codpes,$unidade)*: verifica se aluno (codpes) tem matrícula ativa na pós-graduação da unidade
 - *ativos($unidade)*: retorna *array* de todos alunos de pós-graduação ativos na unicade
 - *ativosCsv($unidade)*: retorna *csv* de todos alunos de pós-graduação ativos na unidade
 
 ### Classe Bempatrimoniado

 - *dump($numpat)*: recebe numpat e retorna todos campos da tabela bempatrimoniado
 
 
