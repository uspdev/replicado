    SELECT L.codpes, L.nompes, F.sglfusclgund AS sglund, C.codcur, C.nomcur, H.codhab, H.nomhab, V.dtainivin, V.codcurgrd
    FROM LOCALIZAPESSOA L
    INNER JOIN VINCULOPESSOAUSP V ON (L.codpes = V.codpes) AND (L.codundclg = V.codclg)
    INNER JOIN CURSOGR C ON (V.codcurgrd = C.codcur)
    INNER JOIN HABILITACAOGR H ON (H.codhab = V.codhab)
    INNER JOIN FUSAOCOLEGIADOUNIDADE F ON (F.codoriclgund = L.codundclg AND F.sta_cg = 'X')
    WHERE (L.codpes = convert(int,:codpes))
        AND (L.tipvin = 'ALUNOGR')
        AND (V.codcurgrd = H.codcur AND V.codhab = H.codhab)
