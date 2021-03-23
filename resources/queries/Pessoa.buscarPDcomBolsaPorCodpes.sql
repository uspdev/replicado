SELECT DISTINCT v.codpes, v.nompes from fflch.dbo.ICTPROJEDITALBOLSA i
inner join fflch.dbo.PDPROJETO p on i.codprj = p.codprj  
inner join fflch.dbo.VINCULOPESSOAUSP v on p.codpes_pd = v.codpes 
where v.tipvin = 'ALUNOPD'
and (p.staatlprj = 'Ativo' OR p.staatlprj = 'Inscrito')
	and
	p.codund = 8 --fflch 
	AND (p.dtafimprj > GETDATE() or p.dtafimprj IS NULL)
and v.codpes  = convert(int,:codpes)