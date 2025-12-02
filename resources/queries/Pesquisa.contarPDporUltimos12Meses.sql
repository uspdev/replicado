SELECT
    /* Formato YYYY-MM sem usar estilo 120 */
    CAST(YEAR(dt) AS VARCHAR(4))
        + '-' +
      RIGHT('0' + CAST(MONTH(dt) AS VARCHAR(2)), 2) AS AnoMes,
    DATENAME(month, dt) AS NomeMes,
    YEAR(dt) AS Ano,
    COUNT(p.codprj) AS qtdProjetosAtivos
FROM (
    /* Geração manual dos últimos 12 meses (0 a 11) */
    SELECT DATEADD(
               month, 
               -Nums.Num,
               DATEADD(day, 1 - DAY(GETDATE()), GETDATE())
           ) AS dt,
           Nums.Num
    FROM (
        SELECT 0 AS Num
        UNION ALL SELECT 1
        UNION ALL SELECT 2
        UNION ALL SELECT 3
        UNION ALL SELECT 4
        UNION ALL SELECT 5
        UNION ALL SELECT 6
        UNION ALL SELECT 7
        UNION ALL SELECT 8
        UNION ALL SELECT 9
        UNION ALL SELECT 10
        UNION ALL SELECT 11
    ) Nums
) Meses
LEFT JOIN PDPROJETO p
    ON p.codund IN (__codundclg__)
   AND p.staatlprj IN (__statuses__)
   AND p.dtainiprj <= DATEADD(day, -1, DATEADD(month, 1, dt))
   AND (p.dtafimprj IS NULL OR p.dtafimprj >= dt)
GROUP BY
    CAST(YEAR(dt) AS VARCHAR(4))
        + '-' +
      RIGHT('0' + CAST(MONTH(dt) AS VARCHAR(2)), 2),
    DATENAME(month, dt),
    YEAR(dt),
    Meses.Num,
    dt
ORDER BY
    YEAR(dt),
    MONTH(dt);
