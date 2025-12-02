SELECT P.nompesttd nompes, M.codpes, M.stamis
FROM MINISTRANTE M
INNER JOIN PESSOA P ON P.codpes = M.codpes
WHERE coddis = :coddis
  AND verdis = CONVERT(INT, :verdis)
  AND codtur = :codtur