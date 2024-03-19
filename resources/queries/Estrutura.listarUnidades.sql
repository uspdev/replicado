SELECT U.*, C.* FROM UNIDADE U
INNER JOIN CAMPUS C ON U.codcam = C.codcam  
WHERE U.dtadtvund IS NULL AND C.stacam = 'O' 
ORDER BY C.nomofccam, U.nomund
