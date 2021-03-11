SELECT DISTINCT 
	l.codpes, 
	l.nompes as NomeAluno, 
	p.codprj, 
	p.titprj, 
	p.fmtapifceprj as BOLSA, 
	p.dtainiprj, 
	p.dtafimprj 
from 
	fflch.dbo.LOCALIZAPESSOA l 
	INNER JOIN 
		fflch.dbo.PDPROJETO p 
		ON l.codpes = p.codpes_pd 
	inner join 
		fflch.dbo.PDPROJETOSUPERVISOR d 
		ON d.codprj = p.codprj 
WHERE 
		l.tipvin = 'ALUNOPD' 
	AND 
		p.staatlprj = 'Ativo' or p.staatlprj = 'Aprovado'
	AND 
		l.sitatl = 'A' 
	AND 
		p.codund = __unidades__
	AND 
		p.codmdl = 2
	and 
		(p.dtafimprj > GETDATE() or p.dtafimprj = null) 
ORDER BY l.nompes 