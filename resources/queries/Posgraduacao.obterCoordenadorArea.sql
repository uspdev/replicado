SELECT L.nompes FROM LOCALIZAPESSOA L
INNER JOIN R10DOCCOOCUR R on (L.codpes = R.codpes)
INNER JOIN AREA A on (A.codcur = R.codcur)
WHERE L.codundclg = convert(int, :codundclg)
AND L.nomfnc = 'Coord Prog Pg'
AND L.sitatl = 'A'
AND A.codare = convert(int, :codare)
