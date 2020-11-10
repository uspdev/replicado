## Métodos da classe Pessoa 

 - *docentes($unidade)*: retorna *array* de todos os docentes ativos na unidade *DEPRECADO - usar listarDocentes()*

 - *ativosVinculo($vinculo, $codundclgi, $contar)*: por padrão retorna *array* com as pessoas ativas de um tipo de vínculo (tipvinext = tipo de vinculo extendido, ex.: Aluno de Pós-Graduação) e também Docente Aposentado, se o terceiro parâmetro *$contar* for igual a 1, retorna um *array* com o índice *total* que corresponde ao número total de pessoas do tipo de vínculo
 
 - *vinculosSetores($codpes, $codundclgi)*: retorna *array* com os vínculos e setores que a pessoa possui
 
 - *nascimento($codpes)*: retorna data de nascimento da pessoa

 - *listarDocentes($codset)*: retorna *array* de todos os docentes ativos na unidade, e se solicitado, de apenas um setor/departamento



