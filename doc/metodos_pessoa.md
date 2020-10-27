## Métodos da classe Pessoa 

 - *dump($codpes, $fields)*: recebe codpes e retorna todos campos da tabela Pessoa para o codpes em questão. 
                           O campos $fields é opcional.
 - *nome($nome)*: recebe uma string nome e retorna os resultados para a tabela Pessoa

 - *telefones($codpes)*: recebe codpes e retorna array com telefones da pessoa

 - *docentes($unidade)*: retorna *array* de todos os docentes ativos na unidade *DEPRECADO - usar listarDocentes()*

 - *servidores($unidade)*: retorna *array* de todos os funcionários ativos na unidade

 - *estagiarios($unidade)*: retorna *array* de todos os estagiários ativos na unidade

 - *tiposVinculos($unidade)*: retorna *array* com os tipos de vínculos *regulares* e também *Docente Aposentado* ('ALUNOGR', 'ALUNOPOS', 'ALUNOCEU', 'ALUNOEAD', 'ALUNOPD', 'SERVIDOR', 'ESTAGIARIORH')

 - *ativosVinculo($vinculo, $codundclgi, $contar)*: por padrão retorna *array* com as pessoas ativas de um tipo de vínculo (tipvinext = tipo de vinculo extendido, ex.: Aluno de Pós-Graduação) e também Docente Aposentado, se o terceiro parâmetro *$contar* for igual a 1, retorna um *array* com o índice *total* que corresponde ao número total de pessoas do tipo de vínculo
 
 - *vinculosSetores($codpes, $codundclgi)*: retorna *array* com os vínculos e setores que a pessoa possui
 
 - *nascimento($codpes)*: retorna data de nascimento da pessoa

 - *listarDocentes($codset)*: retorna *array* de todos os docentes ativos na unidade, e se solicitado, de apenas um setor/departamento



