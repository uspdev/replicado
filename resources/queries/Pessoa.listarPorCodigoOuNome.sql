SELECT P.codpes, P.nompesttd
FROM PESSOA P
WHERE (CAST(P.codpes AS NVARCHAR) LIKE :codpes OR UPPER(P.nompesttd) LIKE UPPER(:nome))
ORDER BY P.nompesttd ASC
