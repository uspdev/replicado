SELECT DISTINCT l.codpes, 
	l.nompes as nome_aluno, 
	p.codprj, 
	p.titprj, 
	p.dtainiprj, 
	p.dtafimprj,
	s.nomset as departamento,
	s.nomabvset as sigla_departamento
from LOCALIZAPESSOA l 
	INNER JOIN PDPROJETO p ON l.codpes = p.codpes_pd 
	inner join SETOR s on s.codset = p.codsetprj 
WHERE l.tipvin = 'ALUNOPD' 
	AND (p.staatlprj = 'Ativo' or p.staatlprj = 'Aprovado')
	AND l.sitatl = 'A' 
	AND p.codund in (__codundclgs__)
	AND p.codmdl = 2
	and (p.dtafimprj > GETDATE() or p.dtafimprj = null) 
ORDER BY l.nompes
