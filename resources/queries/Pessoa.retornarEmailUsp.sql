SELECT DISTINCT codema 
FROM EMAILPESSOA
WHERE codpes = convert(int,:codpes)
AND codema LIKE '%usp.br%'
