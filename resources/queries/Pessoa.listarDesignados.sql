SELECT L.*, P.* FROM LOCALIZAPESSOA L
INNER JOIN PESSOA P ON (L.codpes = P.codpes)
WHERE L.tipvinext = 'Servidor Designado'
    AND L.codundclg IN (__codundclgs__) --automatico do env por getQuery
    AND L.sitatl = 'A'
    AND L.codpes IN
        (SELECT codpes
        FROM LOCALIZAPESSOA L
        WHERE L.tipvinext IN (__tipvinext__)
            AND L.codundclg IN (__codundclgs__)
            AND L.sitatl = 'A')
ORDER BY L.nompes
