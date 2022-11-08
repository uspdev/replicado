SELECT L.codpes, L.nompes, C.codcur, C.nomcur, H.codhab, H.nomhab, V.dtainivin, V.codcurgrd
FROM LOCALIZAPESSOA L
INNER JOIN VINCULOPESSOAUSP V ON (L.codpes = V.codpes) AND (L.codundclg = V.codclg)
INNER JOIN CURSOGR C ON (V.codcurgrd = C.codcur)
INNER JOIN HABILITACAOGR H ON (H.codhab = V.codhab)
WHERE (L.codpes = convert(int,:codpes))
    AND (L.tipvin = 'ALUNOGR' AND L.codundclg IN (__codundclgs__))
    AND (V.codcurgrd = H.codcur AND V.codhab = H.codhab)
