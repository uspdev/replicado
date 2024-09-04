SELECT numtelfmt
FROM LOCALIZAPESSOA
WHERE LOCALIZAPESSOA.codpes = convert(int, :codpes)
AND codfncetr = 0 AND numtelfmt IS NOT NULL