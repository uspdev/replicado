SELECT DISTINCT
    NA.codcur,
    NC.nomcur,
    H.codare,
    NA.nomare,
    H.codpes,
    P.nompesttd AS nompes,
    H.numseqpgm,
    G.nivpgm,
    H.dtaocopgm AS dtaconclusao,
    G.dtadfapgm,
    G.dtadpopgm,
    COALESCE(G.dtadfapgm, H.dtaocopgm) AS dtareferencia
FROM HISTPROGRAMA AS H
INNER JOIN AGPROGRAMA AS G
    ON G.codare = H.codare
    AND G.codpes = H.codpes
    AND G.numseqpgm = H.numseqpgm
INNER JOIN TRABALHOPROG AS T
    ON T.codare = H.codare
    AND T.codpes = H.codpes
    AND T.numseqpgm = H.numseqpgm
INNER JOIN PESSOA AS P ON P.codpes = H.codpes
INNER JOIN NOMEAREA AS NA
    ON NA.codare = H.codare
    AND COALESCE(G.dtadfapgm, H.dtaocopgm) >= NA.dtainiare
    AND (NA.dtafimare IS NULL OR COALESCE(G.dtadfapgm, H.dtaocopgm) <= NA.dtafimare)
LEFT JOIN NOMECURSO AS NC
    ON NC.codcur = NA.codcur
    AND NC.dtafimcur IS NULL
WHERE NA.codcur = CONVERT(int, :codcur)
    AND H.tiphstpgm = 'CON'
    --filtro_inicio--
    --filtro_fim--
ORDER BY dtareferencia DESC, nompes ASC
