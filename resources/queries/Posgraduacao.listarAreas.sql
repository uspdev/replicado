SELECT DISTINCT (A.codare), N.nomare
FROM AREA A
INNER JOIN CURSO C ON A.codcur = C.codcur
INNER JOIN NOMEAREA N ON N.codare = A.codare
INNER JOIN CREDAREA CA ON A.codare = CA.codare
WHERE C.codclg = convert(int, :codundclgi)
AND N.dtafimare IS NULL
