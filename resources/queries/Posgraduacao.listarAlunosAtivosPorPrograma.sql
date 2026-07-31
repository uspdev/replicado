SELECT DISTINCT
    NA.codcur,
    NC.nomcur,
    V.codare,
    NA.nomare,
    V.codpes,
    P.nompesttd AS nompes,
    G.nivpgm,
    V.numseqpgm,
    V.dtainivin,
    G.dtaselpgm,
    G.dtactaprzpgm
FROM VINCULOPESSOAUSP AS V
INNER JOIN AGPROGRAMA AS G
    ON G.codare = V.codare
    AND G.codpes = V.codpes
    AND G.numseqpgm = V.numseqpgm
INNER JOIN PESSOA AS P ON P.codpes = V.codpes
INNER JOIN NOMEAREA AS NA
    ON NA.codare = V.codare
    AND NA.dtafimare IS NULL
LEFT JOIN NOMECURSO AS NC
    ON NC.codcur = NA.codcur
    AND NC.dtafimcur IS NULL
WHERE NA.codcur = CONVERT(int, :codcur)
    AND V.tipvin = 'ALUNOPOS'
    AND V.sitatl = 'A'
    --filtro_codare--
ORDER BY nompes ASC, V.codare ASC
