SELECT DISTINCT (r.codpespgm), (p.nompes), (a.nivpgm), (n.nomare), (a.dtadfapgm)
FROM R39PGMORIDOC r
INNER JOIN PESSOA p  ON r.codpespgm = p.codpes
INNER JOIN NOMEAREA n ON r.codare = n.codare
INNER JOIN AGPROGRAMA a ON a.codpes = r.codpespgm
WHERE r.codpes = convert(int,:codpes) 
AND r.dtafimort IS NOT NULL
AND n.dtafimare IS NOT NULL  
AND a.dtadfapgm IS NOT NULL
AND a.nivpgm IS NOT NULL
AND a.starmtpgm IS NULL
ORDER BY a.nivpgm
