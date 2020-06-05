## Métodos da classe Graduacao

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
