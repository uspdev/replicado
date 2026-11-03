SELECT V.tipvin
FROM VINCULOPESSOAUSP as V
WHERE V.codpes = convert(int, :codpes)
AND V.tipvin = 'SERVIDOR'
AND V.tipfnc = 'Docente'
AND V.sitatl <> 'D'
