SELECT DISTINCT codema 
FROM LOCALIZAPESSOA
WHERE codpes = convert(int,:codpes)
AND codema LIKE '%@usp.br%'