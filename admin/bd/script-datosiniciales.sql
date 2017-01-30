insert into Telefono_Tipo (nombre) values 
	("Móvil"),
	("Casa"),
	("Trabajo"),
	("Fax"),
	("Otro");

insert into Medico (nombre, apellido, usuario, contrasena, fecha_nacimiento, sexo, lugar, direccion, cedula, tipo_cedula) values ("Administrador", "", "root", "root", "1993-03-19", "Masculino", 377, "UD-4 Sector Mucuritas", "21115476", "V");

insert into Telefono (tlf, tipo, medico) values 
	("0412-5558283", 1, 1),
	("0414-2491821", 1, 1),
	("0212-4324831", 1, 1);