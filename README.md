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

## Para testar

 - *verificarEstagioUSP($codpes)*: recebe o número USP da pessoa e retorna true caso ela tenha um estágio na USP e false caso não tenha
 
 - *verificaCoordCursosGrad($codpes)*: retorna true ou false ao verificar pelo nº USP se a pessoa é coordenadora dos cursos de graduação

 - *contarDocentesAtivosPorGenero($sexpes)*: retorna o  total de docentes ativos na unidade com o gênero especificado 

 - *contarEstagiariosAtivosPorGenero($sexpes)*: retorna o total de estágiarios ativos na unidade com gênero especificado

 - *obterEndereco($codpes)*: recebe o número USP da pessoa e retorna o endereço

 - *contarServidoresAtivosPorGenero($sexpes)*: retorna o total de servidores ativos na unidade com gênero especificado

 - *obterTelefones($codpes)*: Método para retornar o telefone principal e o celular da pessoa com número USP especificado

 - *obterNumeroUsp($codema)*: Método que dado um email USP, retorna o número USP da pessoa

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

 - *setorAluno($codpes, $codundclgi)*: retorna *array* com a Sigla do Departamento de Ensino do Aluno de Graduação

 - *contarAtivosPorGenero($sexpes, $codcur = null)*: recebe o gênero (F,M) e o código do curso (opcional) de graduação para retornar a quantidade de alunos de graduação com o gênero especificado

### Classe Posgraduacao

 - *verifica($codpes,$unidade)*: verifica se aluno (codpes) tem matrícula ativa na pós-graduação da unidade

 - *ativos($unidade)*: retorna *array* de todos alunos de pós-graduação ativos na unicade

 - *programas($unidade, $codcur = null)*: retorna *array* dos programas de pós-graduação da unidade ou quando informado o código do curso/programa retorna somente os dados do programa solicitado

 - *orientadores($codare)*: retorna lista dos orientadores credenciados na área de concentração (codare) do programa de pós graduação correspondente.

 - *catalogoDisciplinas($codare)*: retorna *array* do catálogo das disciplinas pertencentes à área de concentração.

 - *disciplina($sgldis)*: retorna *array* contendo todos os dados da disciplina indentificada por sua sigla - sgldis.

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
