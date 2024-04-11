SELECT T.*, E.*, TG.* FROM TITULOPES T 
INNER JOIN ESCOLARIDADE E ON T.codesc = E.codesc
LEFT OUTER JOIN TABGRAUFORM TG ON T.grufor = TG.grufor 
WHERE T.codpes = CONVERT(int, :codpes) 
ORDER BY T.dtatitpes 
