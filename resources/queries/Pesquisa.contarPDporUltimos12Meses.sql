SELECT
    RIGHT(CONVERT(CHAR(7),
        DATEADD(month, -n.Num,
                DATEADD(day, 1 - DAY(GETDATE()), GETDATE())
        ), 120), 7) AS AnoMes,
    DATENAME(month,
        DATEADD(month, -n.Num,
                DATEADD(day, 1 - DAY(GETDATE()), GETDATE())
        )
    ) AS NomeMes,
    YEAR(
        DATEADD(month, -n.Num,
                DATEADD(day, 1 - DAY(GETDATE()), GETDATE())
        )
    ) AS Ano,
    COUNT(p.codprj) AS qtdProjetosAtivos
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
) n
LEFT JOIN PDPROJETO p
    ON p.codund = 44
   AND p.staatlprj IN ('Aprovado', 'Ativo')
   AND p.dtainiprj <= DATEADD(
        day,
        -DAY(
            DATEADD(month, -n.Num, GETDATE())
        ) + 1,
        DATEADD(month, -n.Num + 1, GETDATE())
   )
   AND (
        p.dtafimprj IS NULL OR
        p.dtafimprj >= DATEADD(
            day,
            1 - DAY(GETDATE()),
            DATEADD(month, -n.Num, GETDATE())
        )
   )
GROUP BY
    RIGHT(CONVERT(CHAR(7),
        DATEADD(month, -n.Num,
                DATEADD(day, 1 - DAY(GETDATE()), GETDATE())
        ), 120), 7),
    DATENAME(month,
        DATEADD(month, -n.Num,
                DATEADD(day, 1 - DAY(GETDATE()), GETDATE())
        )
    ),
    YEAR(
        DATEADD(month, -n.Num,
                DATEADD(day, 1 - DAY(GETDATE()), GETDATE())
        )
    )
ORDER BY Ano, AnoMes;
