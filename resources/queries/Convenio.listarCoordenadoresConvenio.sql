SELECT 
    r.codcvn,
    r.codpes,
    p.nompesttd
FROM RESPCONVSERV r
JOIN PESSOA p ON p.codpes = r.codpes
WHERE
    r.codcvn = CONVERT(int, :codcvn)
    AND r.codtiprsp = 1; -- é o coordenador