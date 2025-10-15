SELECT 
    r.codcvn,
    r.codpes,
    r.codunddsp
FROM RESPCONVSERV r
WHERE
    r.codcvn = convert(int,:codcvn)