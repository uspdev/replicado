WITH Limites AS (
    SELECT 
        MIN(YEAR(dtainiprj)) AS AnoInicial,
        YEAR(GETDATE()) AS AnoFinal
    FROM PDPROJETO
    WHERE codund in (__codundclg__)
      AND (staatlprj = 'Aprovado' OR staatlprj = 'Ativo')
),
Anos AS (
    SELECT AnoInicial AS Ano
    FROM Limites
    UNION ALL
    SELECT Ano + 1
    FROM Anos, Limites
    WHERE Ano < AnoFinal
)
SELECT 
    a.Ano,
    COUNT(p.codprj) AS qtdProjetosAtivos
FROM Anos a
LEFT JOIN PDPROJETO p
    ON p.codund in (__codundclg__)
   AND p.staatlprj IN (__statuses__)
   AND YEAR(p.dtainiprj) <= a.Ano
   AND (YEAR(p.dtafimprj) >= a.Ano OR p.dtafimprj IS NULL)
GROUP BY a.Ano
ORDER BY a.Ano
OPTION (MAXRECURSION 0);