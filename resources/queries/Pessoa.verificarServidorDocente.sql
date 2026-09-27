SELECT *
FROM VINCULOPESSOAUSP V
    WHERE tipvin = 'SERVIDOR'
    AND tipfnc = 'Docente'
    AND sitatl = 'A'
    AND codfusclgund = CONVERT(int, :codfusclgund)
    AND codpes = CONVERT(int, :codpes)