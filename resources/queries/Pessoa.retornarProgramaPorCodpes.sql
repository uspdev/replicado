SELECT DISTINCT 
	v.codare, n.nomare 
from 
	VINCULOPESSOAUSP v 
	inner join 
		NOMEAREA n 
		ON v.codare = n.codare
where 
	v.codpes = convert(int,:codpes)