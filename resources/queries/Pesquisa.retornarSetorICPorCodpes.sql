SELECT DISTINCT 
	S.codset, S.nomset 
	from 
		ICTPROJETO I
	INNER JOIN 
		SETOR S 
		ON I.codsetprj = S.codset 
	where 
		S.codund = 8
	AND 
		I.codpesalu = convert(int,:codpesalu)



