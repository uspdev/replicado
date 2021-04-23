--Essa query lista todos os docentes, sejam eles ativos ou aposentados.
SELECT L.codpes, 
	L.nompes, 
    L.nomset, 
    L.codema,
    d.idfpescpq, 
    L.sitatl, 
    P.dtanas, 
    C.dtaflc, 
    L.codset
from LOCALIZAPESSOA L
    INNER JOIN COMPLPESSOA C ON L.codpes = C.codpes 
    INNER JOIN PESSOA P on L.codpes = P.codpes 
    LEFT JOIN DIM_PESSOA_XMLUSP d
    ON L.codpes = d.codpes
where L.tipvinext IN ('Docente Aposentado', 'Docente') 
    AND L.codundclg = __unidades__
ORDER BY L.nompes