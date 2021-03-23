SELECT DISTINCT 
	v.codcurgrd, c.nomcur 
from 
	fflch.dbo.VINCULOPESSOAUSP v 
	inner join 
		fflch.dbo.CURSOGR c 
		on c.codcur = v.codcurgrd 
where v.codpes = convert(int,:codpes)