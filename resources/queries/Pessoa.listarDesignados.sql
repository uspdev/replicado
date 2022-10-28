SELECT L.*, P.* FROM LOCALIZAPESSOA L
INNER JOIN PESSOA P ON (L.codpes = P.codpes)
WHERE (L.tipvinext = 'Servidor Designado'
    AND L.codundclg IN (__codundclg__)
    AND L.sitatl = 'A')
    __tipvinext__
ORDER BY L.nompes
