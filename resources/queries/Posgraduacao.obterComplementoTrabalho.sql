SELECT T.rsutrb, T.palcha, T.rsutrbigl, T.palchaigl
                FROM DDTENTREGATRABALHO T INNER JOIN DDTDEPOSITOTRABALHO D
                ON T.coddpodgttrb = D.coddpodgttrb
                WHERE D.codpes = convert(int, :codpes) AND
                D.dtadpotrb = :dtacad