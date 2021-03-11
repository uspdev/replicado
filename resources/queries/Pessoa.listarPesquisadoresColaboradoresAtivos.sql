 SELECT DISTINCT 
    	l.nompes AS Pesquisador,
    	p.titprj as TituloPesquisa,
    	n.nompes as Resposável,
    	p.staatlprj as Vigência,
    	s.nomset as departamento,
		s.nomabvset as sigla_departamento
    FROM 
    	fflch.dbo.LOCALIZAPESSOA l
		INNER JOIN 
			fflch.dbo.PDPROJETO p ON l.codpes = p.codpes_pd 
		inner join 
			fflch.dbo.PDPROJETOSUPERVISOR d ON d.codprj = p.codprj
		inner join 
			fflch.dbo.PESSOA n ON n.codpes = d.codpesspv 
		inner join 
			fflch.dbo.SETOR s ON s.codset = p.codsetprj
	where 
		l.tipvin = 'PESQUISADORCOLAB'
		AND 
		p.staatlprj = 'Ativo'
		__departamento__ 
	ORDER BY l.nompes 
