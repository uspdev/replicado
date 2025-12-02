SELECT 
    O.sgldis as coddis,
    O.numseqdis, 
    O.numofe,
    STUFF((
        SELECT '; ' + P.nompesttd 
        FROM R32TURMINDOC M2 
        INNER JOIN PESSOA P ON M2.codpes = P.codpes
        WHERE M2.sgldis = O.sgldis 
        AND M2.numseqdis = O.numseqdis 
        AND M2.numofe = O.numofe
        FOR XML PATH('')
    ), 1, 2, '') as todos_ministrantes,
    COUNT(M.codpes) as total_ministrantes_disciplina
FROM OFERECIMENTO O
INNER JOIN R32TURMINDOC M ON O.sgldis = M.sgldis 
                        AND O.numseqdis = M.numseqdis 
                        AND O.numofe = M.numofe
INNER JOIN PESSOA P ON M.codpes = P.codpes
WHERE O.numvagofe > 0
AND O.tipmotcantur IS NULL
AND (O.dtainiofe >= CONCAT(:ano, '-01-01') AND O.dtainiofe <= CONCAT(:ano, '-12-31'))
GROUP BY O.sgldis, O.numseqdis, O.numofe;