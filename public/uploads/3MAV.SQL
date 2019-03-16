CREATE SCHEMA vehiculo;
CREATE SCHEMA persona;
CREATE SCHEMA alistamiento;
CREATE SCHEMA accidente;
CREATE SCHEMA comparendo;
CREATE SCHEMA mantenimiento;

CREATE TABLE vehiculo.marcas
(
 id serial not null,
 nombre character (30) not null,
 idUserRegister integer,
 idUserUpdate integer,
 create_at timestamp,
 updated_at timestamp,
 primary key (id),
 unique (nombre)
);

insert into vehiculo.marcas (id, nombre) values (1,'RENAULT'), (2,'CHEVROLET'), (3,'KIA'), (4,'HYUNDAI'), (5,'NISSAN');


CREATE TABLE vehiculo.servicios
(
 id serial not null,
 nombre character (30) not null,
 idUserRegister integer,
 idUserUpdate integer,
 create_at timestamp,
 updated_at timestamp,
 primary key (id),
 unique (nombre)
);

insert into vehiculo.servicios (id, nombre) values (1,'PUBLICO'), (2,'PARTICULAR'), (3,'DIPLOMATICO'), (4,'OFICIAL'), (5,'ESPECIAL RNMA');

CREATE TABLE vehiculo.clases
(
 id serial not null,
 nombre character (30) not null,
 idUserRegister integer,
 idUserUpdate integer,
 create_at timestamp,
 updated_at timestamp,
 primary key (id),
 unique (nombre)
);

insert into vehiculo.clases (id, nombre) values (1,'AUTOMOVIL'), (2,'BUS'), (3,'BUSETA'), (4,'CAMION'), (5,'CAMIONETA'),
						(6,'CAMPERO'), (7,'CUATRIMOTO'), (8,'DOBLE CABINA'), (9, 'MAQ. CONSTRUCCION O MINERA'), 
						(10, 'MICROBUS'), (11, 'MOTOCARRO'), (12,'MOTOBICICLETA'), (13, 'MOTOTRICICLO'), (14, 'REMOLQUE');

CREATE TABLE vehiculo.organimosTransito
(
 id serial not null,
 nombre character (50) not null,
 idUserRegister integer,
 idUserUpdate integer,
 create_at timestamp,
 updated_at timestamp,
 primary key (id),
 unique (nombre)
);

insert into vehiculo.organimosTransito (id, nombre) values (1,'ALCALDIA MUNICIPAL DE OIBA'), (2,'DEPTO ADMTVO DE TTO Y TTE PUTUMAYO'), 
						(3,'DIR ADMINISTRATIVA PITALITO'), (4,'DIR DPTAL TTOYTTE CASANARE/AGUAZUL'), 
						(5,'DIR DPTAL TTOYTTE SAN ANDRES ISLAS'), (6,'DIR DPTAL TTOYTTE SUCRE/SAMPUE'), 
						(7,'DIR MCPAL TTOYTTE MANZANARES'), (8,'DIR TTEYTTO DPTAL CHOCO/ISTMINA'), 
						(9,'DIR TTEYTTO MCPAL BARBOSA'), (10, 'DIR TTO DPTAL ANTIOQUIA/GUARNE'), 
						(11, 'DIR TTO MCPAL DONMATIAS'), (12, 'DIR TTOYTTE BUCARAMANGA'), (13,'DIR TTOYTTE FLORIDABLANCA'), 
						(14, 'DPTO ADTVO TTEYTTO VILLA DEL ROSARIO'), (15, 'DPTO ADTVO TTOYTTE DIST CARTAGENA'),
						(16,'DPTO ADTVO TTOYTTE GUAJIRA/HATONUEVO');


CREATE TABLE vehiculo.vehiculos
(
  id serial NOT NULL,
  placa character varying(6) NOT NULL,
  interno varchar (5),
  km varchar (20) NOT NULL,
  tipoVinculacion character varying(50) NOT NULL,
  licenciaTransito character varying(50) NOT NULL,
  capacidad varchar (2) NOT NULL,
  marcId integer NOT NULL,
  linea character varying(50) NOT NULL,
  modelo varchar (4) NOT NULL,
  cilindraje character varying(50),
  potencia character varying(50),
  color character varying(50) NOT NULL,
  servId integer NOT NULL,
  clasId integer NOT NULL,
  carroceria varchar (50) NOT NULL,
  combustible character varying(50) NOT NULL,
  puertas varchar (1) NOT NULL,
  numMotor character varying(50),
  vin character varying(50),
  numSerie character varying(50),
  numChasis character varying(50) NOT NULL,
  fechaMatricula date NOT NULL,
  fechaExpedicion date NOT NULL,
  orgTransitoId integer NOT NULL,
  idUserRegister integer,
  idUserUpdate integer,
  create_at timestamp,
  updated_at timestamp,
  PRIMARY KEY (id),
  UNIQUE (placa, interno, licenciaTransito, numMotor, vin, numSerie, numChasis),
  FOREIGN KEY (marcId)
      REFERENCES vehiculo.marcas (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (servId)
      REFERENCES vehiculo.servicios (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (clasId)
      REFERENCES vehiculo.clases (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (orgTransitoId)
      REFERENCES vehiculo.organimosTransito (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT
);

insert into vehiculo.vehiculos 
	values (1,'AL4T26','091','19000','Vehìculo Propio','123456789',5,1,'Acent',2013,'150','150','Rojo',1,1,'Cerrada','Gasolina',2,'123456789','123456789','123456789','123456789','2013-10-12','2013-10-12',2);
insert into vehiculo.vehiculos 
	values (2,'AAA191','9027','200000','Vehìculo Propio','1234567',39,2,'Loga',2010,'150','150','Blanco',1,2,'Marcopolo','ACPM',2,'12345678','12345678','12345678','12345678','2010-01-01','2010-01-01',12);  

CREATE TABLE vehiculo.tipoDocumentosVeh
(
  id serial NOT NULL,
  nombre character (30) NOT NULL,
  duracion integer not null,
  idUserRegister integer,
  idUserUpdate integer,
  create_at timestamp,
  updated_at timestamp,
  unique (nombre),
  PRIMARY KEY (id)
);


insert into vehiculo.tipoDocumentosVeh (id, nombre, duracion) values (1, 'SOAT', 12), (2, 'REVISIÓN TECNICO MECANICA', 12),
									(3, 'POLIZA RCC', 12), (4, 'POLIZA RCE', 12),
									(5, 'TARJETA OPERACIÓN', 12), (6, 'LICENCIA DE TRANSITO', 24);

CREATE TABLE vehiculo.documentosveh
(
  id serial NOT NULL,
  referencia character varying(50) NOT NULL,
  emisor character varying(100) NOT NULL,
  fechaInicio date NOT NULL,
  fechafinal date NOT NULL,
  vehId integer NOT NULL,
  tdvId integer NOT NULL,
  idUserRegister integer,
  idUserUpdate integer,
  create_at timestamp,
  updated_at timestamp,
  unique (referencia),
  PRIMARY KEY (id),
  FOREIGN KEY (tdvId)
      REFERENCES vehiculo.tipoDocumentosveh (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (vehId)
      REFERENCES vehiculo.vehiculos (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT
);

insert into vehiculo.documentosveh (id, referencia, emisor, fechaInicio, fechaFinal, vehId, tdvId) values (1,'012345','QBE SEGUROS', '01-01-2019', '01-01-2019',1,1),
												(2,'0123456','CERTI GASES LOS PATIOS', '01-01-2019', '01-01-2019',1,2);
CREATE TABLE persona.tiposDocumento
(
  id serial NOT NULL,
  nombre varchar (30) NOT NULL,
  idUserRegister integer,
  idUserUpdate integer,
  create_at timestamp,
  updated_at timestamp,
  PRIMARY KEY (id),
  UNIQUE (nombre)
);

insert into persona.tiposDocumento (id, nombre) values (1, 'CÉDULA DE CIUDADANÍA'), (2, 'TARJETA DE IDENTIDAD'), (3, 'NIT'), 
						(5, 'CEDULA DE EXTRANJERÍA'), (6, 'PASAPORTE');

CREATE TABLE persona.personas
(
  id serial NOT NULL,
  numeroDocumento varchar (15) NOT NULL,
  tipoDocumentoId integer NOT NULL,
  nombre character varying(70) NOT NULL,
  apellido character varying(70) NOT NULL,
  genero character varying(20) NOT NULL,
  estadoCivil character varying(30) NOT NULL,
  fechaNacimiento date NOT NULL,
  rh character varying(20),
  nivelEducativo character varying(50),
  profesion character varying(50),
  direccion character varying(50),
  correo character varying(100),
  telefono varchar (15) NOT NULL,
  celular varchar (15) NOT NULL,
  idUserRegister integer,
  idUserUpdate integer,
  create_at timestamp,
  updated_at timestamp,
  PRIMARY KEY (id),
  UNIQUE (numeroDocumento, correo),
  FOREIGN KEY (tipoDocumentoId)
      REFERENCES persona.tiposDocumento (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT
);

insert into persona.personas values ('1','1090489056',1,'Carlos','Waldo','M','Soltero','1994-07-26','O+','Tecnologo','Programador','Trigal','krlosw9@gmail.com','3172891700', '32112234');
insert into persona.personas values ('2','1090234098',1,'Julian','Castro','M','Soltero','1994-07-26','O+','Tecnologo','Programador','Trigal','julian@gmail.com','31228917', '5683995');
insert into persona.personas values ('3','1090109087',1,'Juan','Zapata', 'M','Soltero','1990-01-01','O-','Bachiller','Conductor','Atalaya','juan@gmail.com','3211234567', '3212345677');


CREATE TABLE vehiculo.vehiculosPersonas
(
  id serial NOT NULL,
  vehId integer NOT NULL,
  perId integer NOT NULL,
  descripcion varchar (25),
  idUserRegister integer,
  idUserUpdate integer,
  create_at timestamp,
  updated_at timestamp,
  PRIMARY KEY (id),
  FOREIGN KEY (perId)
      REFERENCES persona.personas (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (vehId)
      REFERENCES vehiculo.vehiculos (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT
);

insert into vehiculo.vehiculosPersonas (id, vehId, perId, descripcion) values (1, '1', '1', 'CONDUCTOR'),
										(2, '1', '1', 'PROPIETARIO');

CREATE TABLE persona.roles
(
  id serial NOT NULL,
  nombre character varying(25) NOT NULL,
  perId integer,
  idUserRegister integer,
  idUserUpdate integer,
  create_at timestamp,
  updated_at timestamp,
  PRIMARY KEY (id),
  FOREIGN KEY (perId)
      REFERENCES persona.personas (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE persona.licencias
(
  id serial NOT NULL,
  numero varchar (15) NOT NULL,
  fechaExpedicion date NOT NULL,
  categoria character varying(3) NOT NULL,
  fechaVencimiento date NOT NULL,
  perId integer NOT NULL,
  idUserRegister integer,
  idUserUpdate integer,
  create_at timestamp,
  updated_at timestamp,
  PRIMARY KEY (id),
  FOREIGN KEY (perId)
      REFERENCES persona.personas (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT
);

insert into persona.licencias (id, numero, fechaexpedicion, categoria, fechavencimiento, perId)
	values (1,'012345', '01-01-2019', 'C2', '01-01-2022','1');

CREATE TABLE persona.tiposDocumentosPer
(
  id serial NOT NULL,
  nombre character varying(25) NOT NULL,
  duracion integer NOT NULL,
  idUserRegister integer,
  idUserUpdate integer,
  create_at timestamp,
  updated_at timestamp,
  unique (nombre),
  PRIMARY KEY (id)
  
);

INSERT INTO persona.tiposDocumentosPer (id, nombre, duracion) VALUES (1, 'SEGURIDAD SOCIAL', 1), (2, 'CARNET', 12);

CREATE TABLE persona.documentosPer
(
  id serial NOT NULL,
  referencia character varying(50) NOT NULL,
  emisor character varying(100) NOT NULL,
  fechaInicio date NOT NULL,
  fechaFinal date NOT NULL,
  perId integer NOT NULL,
  tdpId integer NOT NULL,
  idUserRegister integer,
  idUserUpdate integer,
  create_at timestamp,
  updated_at timestamp,
  unique (referencia),
  PRIMARY KEY (id),
  FOREIGN KEY (perId)
      REFERENCES persona.personas (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (tdpId)
      REFERENCES persona.tiposDocumentosPer (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT
);

insert into persona.documentosper (id, referencia, emisor, fechaInicio, fechaFinal, perId, tdpId)
	values (1, '012345', 'MUTUAL SALUD', '01-03-2019', '01-04-2019', 1, 1 ),
	(2, '0123456', 'COTRANAL', '01-03-2019', '01-03-2020', 1, 2 );

CREATE TABLE persona.contrasenas
(
  id serial NOT NULL,
  pass character varying(50) NOT NULL,
  fecha date NOT NULL,
  perId integer NOT NULL,
  idUserRegister integer,
  idUserUpdate integer,
  create_at timestamp,
  updated_at timestamp,
  PRIMARY KEY (id),
  FOREIGN KEY (perId)
      REFERENCES persona.personas (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE alistamiento.gruposAlistamiento
(
  id serial NOT NULL,
  nombre character varying(70) NOT NULL,
  idUserRegister integer,
  idUserUpdate integer,
  create_at timestamp,
  updated_at timestamp,
  unique (nombre),
  PRIMARY KEY (id)
);

insert into alistamiento.gruposAlistamiento values (1,'Documentos');
insert into alistamiento.gruposAlistamiento values (2,'Luces y direccionales');
insert into alistamiento.gruposAlistamiento values (3,'Limpiaparabrisas');
insert into alistamiento.gruposAlistamiento values (4,'Frenos');

CREATE TABLE alistamiento.tiposAlistamiento
(
  id serial NOT NULL,
  nombre character varying(70) NOT NULL,
  calificacion integer,
  gaId integer NOT NULL,
  idUserRegister integer,
  idUserUpdate integer,
  create_at timestamp,
  updated_at timestamp,
  unique (nombre),
  PRIMARY KEY (id),
  FOREIGN KEY (gaId)
      REFERENCES alistamiento.gruposAlistamiento (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT
);


--Valores de Tipo_alistemiento
insert into alistamiento.tiposAlistamiento values (3,'Licencia de conduccion',5,1);
insert into alistamiento.tiposAlistamiento values (4,'SOAT',15,1);
insert into alistamiento.tiposAlistamiento values (5,'RTM',10,1);
insert into alistamiento.tiposAlistamiento values (7,'Seguros de Daños y RC',10,1);
insert into alistamiento.tiposAlistamiento values (8,'Direccionales Delanteras',5,2);
insert into alistamiento.tiposAlistamiento values (9,'Direccionales Traseras',5,2);
insert into alistamiento.tiposAlistamiento values (10,'Altas',5,2);
insert into alistamiento.tiposAlistamiento values (11,'Bajas',5,2);
insert into alistamiento.tiposAlistamiento values (12,'Stop',5,2);
insert into alistamiento.tiposAlistamiento values (13,'Reversa',5,2);
insert into alistamiento.tiposAlistamiento values (14,'Parqueo',5,2);
insert into alistamiento.tiposAlistamiento values (15,'Der/Izq/Trasero',5,3);
insert into alistamiento.tiposAlistamiento values (16,'Principal',10,4);
insert into alistamiento.tiposAlistamiento values (17,'Emergencia',10,4);

CREATE TABLE alistamiento.informacionAlistamiento
(
  id serial NOT NULL,
  fecha date NOT NULL,
  kilometraje integer NOT NULL,
  calificacion varchar (20) NOT NULL, -- APROBADO - RECHAZADO
  observacionGeneral text,
  vehPlacaId integer NOT NULL,
  perInspectorId integer NOT NULL,
  perConductorId integer NOT NULL,
  idUserRegister integer,
  idUserUpdate integer,
  create_at timestamp,
  updated_at timestamp,
  PRIMARY KEY (id),
  FOREIGN KEY (vehPlacaId)
      REFERENCES vehiculo.vehiculos (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (perInspectorId)
      REFERENCES persona.personas (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (perConductorId)
      REFERENCES persona.personas (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE alistamiento.alistamientos
(
  id serial NOT NULL,
  ccheck character varying(1) NOT NULL,
  observacion character varying(255),
  taId integer NOT NULL,
  infoAlisId integer NOT NULL,
  idUserRegister integer,
  idUserUpdate integer,
  create_at timestamp,
  updated_at timestamp,
  PRIMARY KEY (id),
  FOREIGN KEY (taId)
      REFERENCES alistamiento.tiposAlistamiento (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (infoAlisId)
      REFERENCES alistamiento.informacionAlistamiento (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT
);


CREATE TABLE mantenimiento.operadores
(
	id serial NOT NULL,
	numeroDocumento varchar (15) not null,
	tipoDocumentoId integer not null,
	nombre varchar (50) not null,
	direccion varchar (75) not null,
	telefono varchar (15) NOT NULL,
	celular varchar (15),
	correo varchar(75),
	idUserRegister integer,
	idUserUpdate integer,
	create_at timestamp,
	updated_at timestamp,
	unique (numeroDocumento),
	PRIMARY KEY (id),
	FOREIGN KEY (tipoDocumentoId)
	      REFERENCES persona.tiposDocumento (id) MATCH SIMPLE
	      ON UPDATE CASCADE ON DELETE RESTRICT	
);

insert into mantenimiento.operadores values (1,'123455',3,'Antiguos & Clasicos','Calle 11 #12-83 La playa Cucuta','5826517', '3134567761' ,'antiguos@gmail.com');
insert into mantenimiento.operadores values (2,'123456',3,'OmarCar','av 8 #2-30 Centro','3214567890', '3204346790','omarcar@gmail.com');


CREATE TABLE mantenimiento.mantenimientos
(
	id serial not null,
	tipo varchar (75) not null,
	km varchar (20),
	fecha date not null,
	fechaprox date not null,
	descripcion text,
	vehId integer not null,
	opId integer not null,
	idUserRegister integer,
	idUserUpdate integer,
	create_at timestamp,
	updated_at timestamp,
	PRIMARY KEY (id),
	FOREIGN KEY (opId)
		REFERENCES mantenimiento.operadores(id) MATCH SIMPLE
		ON UPDATE CASCADE ON DELETE RESTRICT,
	FOREIGN KEY (vehId)
	    REFERENCES vehiculo.vehiculos(id) MATCH SIMPLE
	    ON UPDATE CASCADE ON DELETE RESTRICT
);

insert into mantenimiento.mantenimientos values (1, 'PREVENTIVO', '10000', '01-01-2019', '01-06-2019','CAMBIO DE ACEITE', 1, 1);
insert into mantenimiento.mantenimientos values (2, 'CORRECTIVO', '10000', '01-01-2019', '01-06-2019','CAMBIO DE BATERIA', 1, 1);

CREATE TABLE mantenimiento.actividades
(
	id serial not null,
	nombre varchar (150) not null,
	idUserRegister integer,
	idUserUpdate integer,
	create_at timestamp,
	updated_at timestamp,
	unique (nombre),
	PRIMARY KEY (id)	
);

insert into mantenimiento.actividades values (1,'Cambio de Aceite');
insert into mantenimiento.actividades values (2,'Cambio de Bateria');

CREATE TABLE mantenimiento.actividadesMantenimientos
(
	id serial not null,
	acId integer not null,
	mantId integer not null,
	descripcion varchar (255),
	idUserRegister integer,
	idUserUpdate integer,
	create_at timestamp,
	updated_at timestamp,
	PRIMARY KEY (id),	
	FOREIGN KEY (acId)
		REFERENCES mantenimiento.actividades(id) MATCH SIMPLE
		ON UPDATE CASCADE ON DELETE RESTRICT,
	FOREIGN KEY (mantId)
		REFERENCES mantenimiento.mantenimientos(id) MATCH SIMPLE
		ON UPDATE CASCADE ON DELETE RESTRICT
);

INSERT INTO mantenimiento.actividadesMantenimientos values (1, 1, 1, ' ');
INSERT INTO mantenimiento.actividadesMantenimientos values (2, 2, 2, ' ');

CREATE TABLE accidente.accidentesIncidente
(
	id serial not null,
	tipo varchar (75),
	fecha date not null,
	descripcionCorta varchar (75),
	clasificacion varchar (50),
	descripcionLarga text,
	vehId integer not null,
	idUserRegister integer,
	idUserUpdate integer,
	create_at timestamp,
	updated_at timestamp,
	primary key (id),
	FOREIGN KEY (vehId)
		REFERENCES vehiculo.vehiculos(id) MATCH SIMPLE
		ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE comparendo.tiposComparendo
(
	id serial not null,
	nombre varchar (255),
	idUserRegister integer,
	idUserUpdate integer,
	create_at timestamp,
	updated_at timestamp,
	PRIMARY KEY (id),
	UNIQUE (nombre)
);

insert into comparendo.tiposComparendo (id, nombre) 
	values (1, 'No respetar las señales de tránsito'),
	(2, 'Exceso de velocidad'),
	(3, 'No pagar el peaje en los sitios establecidos'),
	(4, 'Abandonar un vehículo de servicio público con pasajeros');

CREATE TABLE comparendo.comparendos
(
	id serial not null,
	tipComId integer not null,
	fecha date not null,
	lugar varchar (75) not null,
	descripcion varchar (255) not null,
	estado varchar (50) not null,
	vehId integer not null,
	idUserRegister integer,
	idUserUpdate integer,
	create_at timestamp,
	updated_at timestamp,
	primary key (id),
	FOREIGN KEY (tipComId)
	    REFERENCES comparendo.tiposComparendo(id) MATCH SIMPLE
	    ON UPDATE CASCADE ON DELETE RESTRICT,
	FOREIGN KEY (vehId)
	    REFERENCES vehiculo.vehiculos(id) MATCH SIMPLE
	    ON UPDATE CASCADE ON DELETE RESTRICT
);

insert into comparendo.comparendos values (1, 1,'01-01-2019', 'Pamplona', 'Alta velocidad', 'Pago', 1);
