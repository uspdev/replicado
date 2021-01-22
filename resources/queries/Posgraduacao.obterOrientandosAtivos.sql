-- Orientando Ativos
SELECT DISTINCT (r.codpespgm), (v.nompes), (v.nivpgm), (n.nomare)
FROM R39PGMORIDOC r
INNER JOIN VINCULOPESSOAUSP v  ON r.codpespgm = v.codpes
INNER JOIN NOMEAREA n ON r.codare = n.codare
WHERE r.codpes = convert(int,:codpes) 
AND r.dtafimort = NULL  
AND n.dtafimare = NULL 
AND v.nivpgm IS NOT NULL
ORDER BY v.nompes