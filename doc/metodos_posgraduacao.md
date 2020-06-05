## Métodos da classe Posgraduacao

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