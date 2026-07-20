SELECT DISTINCT
    NA.codcur,
    NC.nomcur,
    R.codare,
    NA.nomare,
    R.codpes,
    P.nompesttd AS nompes,
    R.nivare,
    R.tiport,
    CASE
        WHEN R.dtavalini >= NA.dtainiare THEN R.dtavalini
        ELSE NA.dtainiare
    END AS dtavalini,
    CASE
        WHEN R.dtavalfim IS NULL THEN NA.dtafimare
        WHEN NA.dtafimare IS NULL THEN R.dtavalfim
        WHEN R.dtavalfim <= NA.dtafimare THEN R.dtavalfim
        ELSE NA.dtafimare
    END AS dtavalfim,
    R.dtavalini AS dtavalinicrd,
    R.dtavalfim AS dtavalfimcrd,
    NA.dtainiare,
    NA.dtafimare,
    R.dtaaprccp,
    R.dtaaprcpg,
    R.dtaaprcog,
    R.stardmort
FROM R25CRECREDOC AS R
INNER JOIN PESSOA AS P ON P.codpes = R.codpes
INNER JOIN NOMEAREA AS NA
    ON NA.codare = R.codare
    AND (NA.dtafimare IS NULL OR R.dtavalini <= NA.dtafimare)
    AND (R.dtavalfim IS NULL OR R.dtavalfim >= NA.dtainiare)
LEFT JOIN NOMECURSO AS NC
    ON NC.codcur = NA.codcur
    AND NC.dtafimcur IS NULL
WHERE NA.codcur = CONVERT(int, :codcur)
