## Métodos da classe Pessoa 

 - *dump($codpes, $fields)*: recebe codpes e retorna todos campos da tabela Pessoa para o codpes em questão. 
                           O campos $fields é opcional.
 - *nome($nome)*: recebe uma string nome e retorna os resultados para a tabela Pessoa

 - *cracha($codpes)*: recebe codpes e retorna todos campos da tabela catr_cracha para o codpes em questão 

 - *telefones($codpes)*: recebe codpes e retorna array com telefones da pessoa

 - *docentes($unidade)*: retorna *array* de todos os docentes ativos na unidade *DEPRECADO - usar listarDocentes()*

 - *servidores($unidade)*: retorna *array* de todos os funcionários ativos na unidade

 - *estagiarios($unidade)*: retorna *array* de todos os estagiários ativos na unidade

 - *totalVicunlo($vinculo,$unidade)*: retorna *total* de vínculo ativos na unidade

 - *totalPosNivelPrograma($nivpgm,$unidade)*: retorna *total de aluno de Pós matriculados* de acordo com o nível do programa, na unidade

 - *tiposVinculos($unidade)*: retorna *array* com os tipos de vínculos *regulares* e também *Docente Aposentado* ('ALUNOGR', 'ALUNOPOS', 'ALUNOCEU', 'ALUNOEAD', 'ALUNOPD', 'SERVIDOR', 'ESTAGIARIORH')

 - *ativosVinculo($vinculo, $codundclgi, $contar)*: por padrão retorna *array* com as pessoas ativas de um tipo de vínculo (tipvinext = tipo de vinculo extendido, ex.: Aluno de Pós-Graduação) e também Docente Aposentado, se o terceiro parâmetro *$contar* for igual a 1, retorna um *array* com o índice *total* que corresponde ao número total de pessoas do tipo de vínculo
 
 - *vinculosSetores($codpes, $codundclgi)*: retorna *array* com os vínculos e setores que a pessoa possui
 
 - *nascimento($codpes)*: retorna data de nascimento da pessoa

 - *contarDocentesAtivosPorGenero($sexpes)*: retorna o  total de docentes ativos na unidade com o gênero especificado 

 - *contarEstagiariosAtivosPorGenero($sexpes)*: retorna o total de estágiarios ativos na unidade com gênero especificado

 - *obterEndereco($codpes)*: recebe o número USP da pessoa e retorna o endereço

 - *listarDocentes($codset)*: retorna *array* de todos os docentes ativos na unidade, e se solicitado, de apenas um setor/departamento

 - *contarServidoresAtivosPorGenero($sexpes)*: retorna o total de servidores ativos na unidade com gênero especificado


