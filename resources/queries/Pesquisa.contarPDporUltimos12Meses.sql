WITH Ultimos12Meses AS (
    SELECT 
        DATEADD(MONTH, -11, DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1)) AS DataInicio,
        DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1) AS DataFim
),
Meses AS (
    SELECT 0 AS OffsetMes
    UNION ALL
    SELECT OffsetMes + 1 
    FROM Meses 
    WHERE OffsetMes < 11
)
SELECT 
    FORMAT(DATEADD(MONTH, m.OffsetMes, u.DataInicio), 'yyyy-MM') AS AnoMes,
    DATENAME(MONTH, DATEADD(MONTH, m.OffsetMes, u.DataInicio)) AS nomeMes,
    YEAR(DATEADD(MONTH, m.OffsetMes, u.DataInicio)) AS Ano,
    COUNT(p.codprj) AS qtdProjetosAtivos
FROM Ultimos12Meses u
CROSS APPLY Meses m
LEFT JOIN PDPROJETO p
    ON p.codund in (__codundclg__)
   AND p.staatlprj IN (__statuses__)
   AND p.dtainiprj <= EOMONTH(DATEADD(MONTH, m.OffsetMes, u.DataInicio))
   AND (p.dtafimprj IS NULL OR p.dtafimprj >= DATEADD(MONTH, m.OffsetMes, u.DataInicio))
GROUP BY 
    DATEADD(MONTH, m.OffsetMes, u.DataInicio),
    FORMAT(DATEADD(MONTH, m.OffsetMes, u.DataInicio), 'yyyy-MM'),
    DATENAME(MONTH, DATEADD(MONTH, m.OffsetMes, u.DataInicio)),
    YEAR(DATEADD(MONTH, m.OffsetMes, u.DataInicio))
ORDER BY 
    DATEADD(MONTH, m.OffsetMes, u.DataInicio)
OPTION (MAXRECURSION 0);
