SELECT C.codcurceu,  O.codofeatvceu, C.codsetdep, S.nomset, C.nomcurceu, O.qtdvagofe, 
	C.dscpbcinr, C.objcur, C.juscur, C.fmtcurceu, O.dtainiofeatv, O.dtafimofeatv, 
	C.juscurensdtc, P.cgaminapralu, P.totcgahorpgm 
	from CURSOCEU C 
        INNER JOIN SETOR S ON C.codsetdep = S.codset 
        INNER JOIN OFERECIMENTOATIVIDADECEU O ON O.codcurceu = C.codcurceu 
        LEFT JOIN PROGRAMACURSOCEU P ON C.codcurceu = P.codcurceu 
        WHERE C.codclg in (__unidades__) 
        __ano__
        __departamento__ 
	ORDER BY C.dtainc ASC 