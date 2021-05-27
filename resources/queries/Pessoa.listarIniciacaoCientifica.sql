SELECT 
	ic.codprj as cod_projeto,
	ic.codpesalu as aluno,
	p1.nompes as nome_aluno,
	ic.titprj as titulo_pesquisa,
	ic.codpesrsp as orientador, 
	p2.nompes as nome_orientador,
	ic.dtainiprj as data_ini,
	ic.dtafimprj as data_fim, 
	ic.anoprj as ano_projeto,
	s.nomset as departamento,
	s.nomabvset as sigla_departamento,
	ic.staprj as status_projeto
from 
	ICTPROJETO ic
	inner join 
		PESSOA p1
		on p1.codpes = ic.codpesalu
	inner join 
		PESSOA p2
		on p2.codpes = ic.codpesrsp
	inner join 
		SETOR s ON s.codset = ic.codsetprj 
where 
	ic.codundprj in (__unidades__) 
	__data__
	__departamento__ 
	ORDER BY p1.nompes