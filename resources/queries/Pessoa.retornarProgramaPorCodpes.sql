SELECT DISTINCT 
	v.codare, n.nomare 
from 
	fflch.dbo.VINCULOPESSOAUSP v 
	inner join 
		fflch.dbo.NOMEAREA n 
		ON v.codare = n.codare
where 
	v.codpes = convert(int,:codpes)