SELECT * FROM VINCULOPESSOAUSP V
    WHERE sitatl = 'A'
    AND tipvin = 'SERVIDOR'
    AND tipfnc != 'Docente'
    AND codfusclgund = CONVERT(int, :codfusclgund)
    AND codpes = CONVERT(int, :codpes)