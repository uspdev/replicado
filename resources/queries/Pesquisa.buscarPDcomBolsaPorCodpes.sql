SELECT DISTINCT v.codpes, v.nompes from ICTPROJEDITALBOLSA i
inner join PDPROJETO p on i.codprj = p.codprj  
inner join VINCULOPESSOAUSP v on p.codpes_pd = v.codpes 
where v.tipvin = 'ALUNOPD'
and (p.staatlprj = 'Ativo' OR p.staatlprj = 'Inscrito')
	and
	p.codund in (__codundclgs__) 
	AND (p.dtafimprj > GETDATE() or p.dtafimprj IS NULL)
and v.codpes  = convert(int,:codpes)