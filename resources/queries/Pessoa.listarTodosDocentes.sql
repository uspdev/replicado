--Essa query lista todos os docentes, sejam eles ativos ou aposentados.
SELECT LOCALIZAPESSOA.codpes, 
	LOCALIZAPESSOA.nompes, 
    LOCALIZAPESSOA.nomset, 
    LOCALIZAPESSOA.codema,
    DIM_PESSOA_XMLUSP.idfpescpq, 
    LOCALIZAPESSOA.sitatl, 
    P.dtanas, 
    C.dtaflc, 
    LOCALIZAPESSOA.codset
from LOCALIZAPESSOA
    INNER JOIN dbo.COMPLPESSOA C ON LOCALIZAPESSOA.codpes = C.codpes 
    INNER JOIN dbo.PESSOA P on LOCALIZAPESSOA.codpes = P.codpes 
    LEFT JOIN DIM_PESSOA_XMLUSP
    ON LOCALIZAPESSOA.codpes = DIM_PESSOA_XMLUSP.codpes
where (LOCALIZAPESSOA.tipvinext = 'Docente Aposentado' 
	or tipvinext = 'Docente') and LOCALIZAPESSOA.codundclg = __unidades__
ORDER BY LOCALIZAPESSOA.nompes