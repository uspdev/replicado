SELECT A.codpes, A.codare, A.nivpgm, A.numseqpgm, A.dtadpopgm, P.nompesttd, L.tipvin, L.sitatl, L.codundclg
FROM AGPROGRAMA AS A
INNER JOIN LOCALIZAPESSOA L ON (A.codpes = L.codpes)
INNER JOIN PESSOA P ON (A.codpes = P.codpes)
WHERE A.codpes = CONVERT(int, :codpes)