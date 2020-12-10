disk init 
name = "replicado_data", 
physname = "/opt/replicado_data.dat", 
size = "1G"
go

disk init 
name = "replicado_log", 
physname = "/opt/replicado_log.dat", 
size = "1G"
go

CREATE DATABASE replicado ON replicado_data='1G' LOG ON replicado_log='1G'
go
