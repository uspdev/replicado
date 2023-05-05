SELECT *
FROM LOCALIZAPESSOA
WHERE codpes = convert(int,:codpes)
--designados-- AND tipdsg is NULL
