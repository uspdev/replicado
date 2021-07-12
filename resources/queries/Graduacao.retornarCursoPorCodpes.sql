SELECT DISTINCT 
	v.codcurgrd, c.nomcur 
from 
	VINCULOPESSOAUSP v 
	inner join 
		CURSOGR c 
		on c.codcur = v.codcurgrd 
where v.codpes = convert(int,:codpes)