SELECT DISTINCT
  H.codhab,
  LTRIM(RTRIM(H.nomhab)) AS nomhab,
  H.perhab AS periodo
FROM CURSOGR C
INNER JOIN HABILITACAOGR H
  ON C.codcur = H.codcur
  WHERE C.codclg = CONVERT(int, :codundclg)
  AND C.codcur = CONVERT(int, :codcur)
  AND C.dtadtvcur IS NULL
  AND H.dtadtvhab IS NULL
  AND H.codhab IS NOT NULL
  AND H.nomhab IS NOT NULL
  AND LTRIM(RTRIM(H.nomhab)) <> ''
ORDER BY H.nomhab ASC