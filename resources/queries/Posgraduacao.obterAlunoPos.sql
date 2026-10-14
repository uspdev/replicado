SELECT A.codpes, A.codare, A.nivpgm, A.numseqpgm, A.dtadpopgm, P.nompesttd
FROM AGPROGRAMA AS A
INNER JOIN LOCALIZAPESSOA L ON (A.codpes = L.codpes)
INNER JOIN PESSOA P ON (A.codpes = P.codpes)
WHERE A.codpes = convert(int, :codpes)
 AND A.stacsldfatrb = 'N'
 AND L.tipvin = 'ALUNOPOS'
 AND L.sitatl = 'A'
 AND L.codundclg = convert(int, :codundclg)
