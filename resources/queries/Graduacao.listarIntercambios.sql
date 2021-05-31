SELECT DISTINCT I.codpes, O.nomorgpnt, P.nompas from INTERCAMBIOUSPORGAO I
    INNER JOIN LOCALIZAPESSOA L ON I.codpes = L.codpes 
    INNER JOIN ORGAOPRETENDENTE O ON I.codorg = O.codorg
    INNER JOIN PAIS P ON O.codpas = P.codpas
    WHERE L.tipvin = 'ALUNOGR'
    AND I.dtafimitb > GETDATE() 
    AND L.codundclg IN (__codundclgi__)