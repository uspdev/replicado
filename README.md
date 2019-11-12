## Replicado

Biblioteca PHP que abstrai o acesso aos dados do replicado USP, 
isto é, ao invés de inserir uma consulta SQL diretamente em seu código, 
como por exemplo: 

    SELECT codpes,nompes,... FROM pessoa WHERE codpes='123'

Usa-se uma classe PHP que faz a abstração do acesso e portanto deixa 
seu código muito mais limpo e torna as consultas reutilizáveis:

    Pessoa::dump('123')

## Adicione essa lib em seu projeto PHP:

    composer require uspdev/replicado

## Dependências

É necessário pelo menos o PHP v7.0.

Esta bliboteca precisa da extensão ```ext-sybase```. No ubuntu instale com

    sudo apt install php-sybase

## Para testar:

Rode na linha de comando

    php test/run.php credentials.php

Se preferir rode alguns exemplos

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

    $emails = Pessoa::emails('123456');
    print_r($emails);
```

## Informações sobre tabelas:

   [https://uspdigital.usp.br/replunidade](https://uspdigital.usp.br/replunidade)


## Contribuindo com este projeto:

Veja [aqui](doc/contrib.md) algumas orientações de como contribuir.


## Métodos existentes:

### Classe Pessoa 

 - *dump($codpes, $fields)*: recebe codpes e retorna todos campos da tabela Pessoa para o codpes em questão. 
                           O campos $fields é opcional.
 - *nome($nome)*: recebe uma string nome e retorna os resultados para a tabela Pessoa

 - *nomeCompleto($codpes)*: recebe codpes e retorna o nome completo (nome social)

 - *cracha($codpes)*: recebe codpes e retorna todos campos da tabela catr_cracha para o codpes em questão 

 - *email($codpes)*: recebe codpes e retorna email de correspondência da pessoa

 - *emails($codpes)*: recebe codpes e retorna todos emails da pessoa

 - *emailusp($codpes)*: recebe codpes e retorna email usp da pessoa

 - *vinculos($codpes)*: retorna vínculos ativos da pessoa

 - *vinculosSiglas($codpes,$unidade)*: retorna siglas de vínculos ativos da pessoa em uma dada unidade

 - *docentes($unidade)*: retorna *array* de todos os docentes ativos na unidade

 - *servidores($unidade)*: retorna *array* de todos os funcionários ativos na unidade

 - *estagiarios($unidade)*: retorna *array* de todos os estagiários ativos na unidade

 - *totalVicunlo($vinculo,$unidade)*: retorna *total* de vínculo ativos na unidade

 - *totalPosNivelPrograma($nivpgm,$unidade)*: retorna *total de aluno de Pós matriculados* de acordo com o nível do programa, na unidade

 - *tiposVinculos($unidade)*: retorna *array* com os tipos de vínculos *regulares* ('ALUNOGR', 'ALUNOPOS', 'ALUNOCEU', 'ALUNOEAD', 'ALUNOPD', 'SERVIDOR', 'ESTAGIARIORH')

 - *ativosVinculo($vinculo, $codundclgi)*: retorna *array* com as pessoas ativas de um tipo de vínculo 

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

 - *programa($codpes)*: recebe o nº USP do aluno *int* e retorna *int* com o código do programa

 - *disciplinasConcluidas($codpes, $unidade)*: recebe o nº USP do aluno *int* e o código da unidade *int* e retorna *array* com os códigos, status e créditos de todas as disciplinas concluidas pelo aluno

 - *creditosDisciplina($coddis)*: recebe o código da disciplina *string* e retorna *int* com a quantidade de créditos da disciplina

 - *creditosDisciplinasConcluidasAproveitamentoEstudosExterior($codpes, $unidade)*: recebe o nº USP do aluno *int* e o código da unidade *int* e retorna *array* com os códigos e créditos atribuídos das disciplinas livres concluídas pelo aluno no exterior

 - *disciplinasCurriculo($codcur, $codhab)*: recebe o código do curso *string* e o código da habilitação *int* e retorna *array* com os códigos, nomes, versões, semestres ideias e obrigatoriedade das disciplinas do curriculo (grade curricular) atual do JupiterWeb
coddis, verdis, tipobg, coddis_equivalente, verdis_equivalente 

 - *disciplinasEquivalentesCurriculo($codcur, $codhab)*: recebe o código do curso *string* e o código da habilitação *int* e retorna *array* com código da equivalência, os códigos, versões e obrigatoriedade das disciplinas e os códigos e versões de suas respectivas equivalências, em relação ao curriculo (grade curricular) atual do JupiterWeb

### Classe Posgraduacao

 - *verifica($codpes,$unidade)*: verifica se aluno (codpes) tem matrícula ativa na pós-graduação da unidade

 - *ativos($unidade)*: retorna *array* de todos alunos de pós-graduação ativos na unicade

 - *programas($unidade, $codcur = null)*: retorna *array* dos programas de pós-graduação da unidade ou quando informado o código do curso/programa retorna somente os dados do programa solicitado

 - *orientadores($codare)*: retorna lista dos orientadores credenciados na área de concentração (codare) do programa de pós graduação correspondente.

 - *catalogoDisciplinas($codare)*: retorna *array* do catálogo das disciplinas pertencentes à área de concentração.

 - *disciplina($sgldis)*: retorna *array* contendo todos os dados da disciplina indentificada por sua sigla - sgldis.

 - *disciplinasOferecimento($codare{, $data opcional})*: Retorna a lista de disciplinas em oferecimento no semestre de uma determinada área de concentração.

 - *oferecimento($sgldis, $numofe)*: Retorna dados de um oferecimento de disciplina incluindo local e ministrante.

 - *espacoturma($sgldis, $numseqdis, $numofe)*: Retorna local e horário dos oferecimentos da disciplina. É usado no contexto do oferecimento.

 - *ministrante($sgldis, $numseqdis, $numofe)*: Retorna lista de ministrantes da disciplina. É usado no contexto do oferecimento.

 - *areasProgramas(int $codundclgi, int $codcur = null)*: Retorna as áreas de concentração ativas dos programas de pós-graduação da unidade. Se informado o código do curso (programa), retorna apenas as áreas deste curso.

 - *alunosPrograma(int $codundclgi, int $codcur, int $codare = null)*: Retorna os alunos ativos de um programa (codcur) de pós da unidade (codundclgi), se pedido, somente de uma área (codare). 

 - *egressosArea(int $codare)*: Retorna lista de alunos que defenderam pós-graduação em determinada área.

 ### Classe Bempatrimoniado

 - *dump($numpat, $cols)*: recebe numpat e retorna todos campos da tabela bempatrimoniado
 - *verifica($numpat)*: recebe numpat e retorna true se o bem está ativo

 - *bens(array $filtros = [], array $buscas = [], array $tipos = [], int $limite = 2000)*: retorna todos campos da tabela BEMPATRIMONIADO dos patrimônios. Utilizar $filtros para valores exatos, $buscas com o *LIKE* e $tipos para colunas que precisam de convert. Por padrão, retorna 2000 registros.

 - *ativos(array $filtros = [], array $buscas = [], array $tipos = [], int $limite = 2000)*: retorna todos campos da tabela BEMPATRIMONIADO dos patrimônios ATIVOS. Utilizar $filtros para valores exatos, $buscas com o *LIKE* e $tipos para colunas que precisam de convert. Em $filtros já é adicionado por padrão o 'stabem' = 'Ativo'. Por padrão, retorna 2000 registros.

  * Exemplo utilização: 
  ```php
        $filtros = [
            'codpes' => 11111111,
            'codlocusp' => 11111,
        ];
        $buscas = [
            'epfmarpat' => 'MARCA',
            'modpat' => 'CORE 2 DUO',
        ];
        $tipos = [
            'codpes' => 'int',
            'codlocusp' => 'numeric',
        ]
        $bens = Bempatrimoniado::bens($filtros, $buscas, $tipos); 
        $ativos = Bempatrimoniado::ativos($filtros, $buscas, $tipos);
  ```
 
