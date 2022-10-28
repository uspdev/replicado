SELECT COUNT(*) total
    FROM PESSOA P
    INNER JOIN LOCALIZAPESSOA L ON (P.codpes = L.codpes)
    WHERE L.codset IN (__codset__)
        AND L.codfncetr = 0 -- exclui designados
        __filtroAposentados__
