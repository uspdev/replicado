SELECT 
    COUNT(m.codpes) AS matriculados 
FROM 
    MATRICULACURSOCEU m 
WHERE 
    m.codcurceu = convert(int,:codcurceu)
    AND m.codedicurceu = convert(int,:codedicurceu) 