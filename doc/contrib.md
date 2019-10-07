## Recomendações para contribuir com este projeto

O replicado pode consultar tanto o MSSQL quanto o sybase-ase e em diversas versões diferentes. Dessa forma é necessário manter a compatibilidade com os diversos replicados das unidades.

* Proure abrir uma issue antes de começar a mexer no código.
* As contribuições serão aceitas por meio de pull requests.
* Ao criar um novo método atente para a documentação, tanto no código quanto no readme.
* A branch master é consderada estável e em geral é usado em produção. 
* Os releases não são muito regulares, daí novamente usar a branch master.

### SQL

* Deve-se usar ```getdate()``` ao invés de ```current_timestamp```
* Se um parâmetro passado for int deve-se fazer a conversão com ```convert(int, :param)```

Até então algumas queries estão assim

```
$query = "SELECT *";
$query .= " FROM PESSOA as p";
$query .= " WHERE p.codpes = :codpes";
```

Vamos procurar fazer assim
```
$query = "SELECT *
    FROM PESSOA as p
    WHERE p.codpes = convert(int, :codpes)
";
```

Fica mais conciso e facilita copiar e colar de um frontend de SGBD.