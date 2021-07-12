SELECT DISTINCT (nomabvset) 

FROM LOCALIZAPESSOA

WHERE codpes = convert(int,:codpes)
    AND sitatl = 'A' 
    AND codundclg IN (__unidades__)