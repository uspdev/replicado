SELECT 
    anos.Ano,
    COUNT(p.codprj) AS qtdProjetosAtivos
FROM (
    SELECT DISTINCT YEAR(dtainiprj) AS Ano
    FROM PDPROJETO
    WHERE codund IN (__codundclg__)
      AND staatlprj IN (__statuses__)
      AND dtainiprj IS NOT NULL
      AND YEAR(dtainiprj) <= YEAR(GETDATE())
    UNION
    SELECT DISTINCT YEAR(dtafimprj) AS Ano
    FROM PDPROJETO
    WHERE codund IN (__codundclg__)
      AND staatlprj IN (__statuses__)
      AND dtafimprj IS NOT NULL
      AND YEAR(dtafimprj) <= YEAR(GETDATE())
) anos
LEFT JOIN PDPROJETO p
    ON p.codund IN (__codundclg__)
   AND p.staatlprj IN (__statuses__)
   AND p.dtainiprj IS NOT NULL
   AND YEAR(p.dtainiprj) <= anos.Ano
   AND (
        p.dtafimprj IS NULL 
        OR YEAR(p.dtafimprj) >= anos.Ano
   )
GROUP BY anos.Ano
ORDER BY anos.Ano;
