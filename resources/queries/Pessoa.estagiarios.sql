 SELECT LOCALIZAPESSOA.*, PESSOA.*__select__ FROM LOCALIZAPESSOA
    INNER JOIN PESSOA ON (LOCALIZAPESSOA.codpes = PESSOA.codpes)
    __emailpessoa__
    WHERE (LOCALIZAPESSOA.tipvin = 'ESTAGIARIORH'
        AND LOCALIZAPESSOA.codundclg IN (__unidades__)
        AND LOCALIZAPESSOA.sitatl = 'A'
        __codema__)
    ORDER BY LOCALIZAPESSOA.nompes