SELECT DISTINCT
    t1.codpes,
    t2.tipbnfalu,
    t1.codslamon
FROM
    BENEFICIOALUCONCEDIDO t1
    INNER JOIN BENEFICIOALUNO t2 ON t1.codbnfalu = t2.codbnfalu
    AND t1.dtafimccd > GETDATE ()
    AND t1.dtacanccd IS NULL
    AND t2.codbnfalu = 32
    AND t1.codslamon IN (__codslamon__)
