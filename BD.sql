
CREATE DATABASE sistema_gestion_novedades;
USE sistema_gestion_novedades;


-- =========================
-- TABLA ROLES
-- =========================
CREATE TABLE ROLES (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL
);

-- =========================
-- TABLA PERSONAS (NUEVA)
-- =========================
CREATE TABLE PERSONAS (
    id_persona INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(120),
    tipo_documento VARCHAR(10),
    numero_documento VARCHAR(30) UNIQUE,
    edad DATE,
    telefono VARCHAR(20),
    correo VARCHAR(100)
);

-- =========================
-- TABLA USUARIOS
-- =========================
CREATE TABLE USUARIOS (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT,
    contraseña VARCHAR(100),
    id_rol INT,
    estado VARCHAR(20),

    FOREIGN KEY (id_persona) REFERENCES PERSONAS(id_persona),
    FOREIGN KEY (id_rol) REFERENCES ROLES(id_rol)
);

-- =========================
-- TABLA RESIDENTES
-- =========================
CREATE TABLE RESIDENTES (
    id_residente INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT,
    profesion VARCHAR(100) NULL,

    FOREIGN KEY (id_persona) REFERENCES PERSONAS(id_persona)
);


-- =========================
-- TABLA PROPIETARIOS
-- =========================
CREATE TABLE PROPIETARIOS (
    id_propietario INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT,
    direccion_residencia VARCHAR(150),

    FOREIGN KEY (id_persona) REFERENCES PERSONAS(id_persona)
);

-- =========================
-- TABLA EMPLEADOS
-- =========================
CREATE TABLE EMPLEADOS (
    id_empleado INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT,
    cargo VARCHAR(100),
    especialidad VARCHAR(100),
    turno VARCHAR(50),
    estado_laboral VARCHAR(20),

    FOREIGN KEY (id_persona) REFERENCES PERSONAS(id_persona)
);

-- =========================
-- TABLA TORRES (NUEVA)
-- =========================
CREATE TABLE TORRES (
    id_torre INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20)
);

-- =========================
-- TABLA INMUEBLES
-- =========================
CREATE TABLE INMUEBLES (
    id_inmueble INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(10),
    id_torre INT,
    area DECIMAL(10,2),
    parqueadero VARCHAR(20),
    id_propietario INT,

    FOREIGN KEY (id_torre) REFERENCES TORRES(id_torre),
    FOREIGN KEY (id_propietario) REFERENCES PROPIETARIOS(id_propietario)
);

ALTER TABLE INMUEBLES 
ADD total_personas INT,
ADD total_adultos INT,
ADD total_menores INT;

-- =========================
-- TABLA RESIDENTE_INMUEBLE 
-- =========================
CREATE TABLE RESIDENTE_INMUEBLE (
    id_residente INT ,
    id_inmueble INT,
    fecha_ingreso DATE,

    PRIMARY KEY (id_residente,id_inmueble),

    FOREIGN KEY (id_residente) REFERENCES RESIDENTES(id_residente),
    FOREIGN KEY (id_inmueble) REFERENCES INMUEBLES(id_inmueble)
);

-- =========================
-- TABLA MASCOTAS
-- =========================
CREATE TABLE MASCOTAS (
    id_mascota INT AUTO_INCREMENT PRIMARY KEY,
    id_residente INT,
    tipo VARCHAR(50),
    raza VARCHAR(100),
    cantidad INT,

    FOREIGN KEY (id_residente) REFERENCES RESIDENTES(id_residente)
);

-- =========================
-- TABLA CONTACTOS DE EMERGENCIA
-- =========================
CREATE TABLE CONTACTOS_DE_EMERGENCIA (
    id_contacto INT AUTO_INCREMENT PRIMARY KEY,
    id_residente INT,
    nombre VARCHAR(120),
    telefono VARCHAR(20),
    relacion VARCHAR(50),

    FOREIGN KEY (id_residente) REFERENCES RESIDENTES(id_residente)
);

-- =========================
-- TABLA CATEGORIAS NOVEDAD
-- =========================
CREATE TABLE CATEGORIAS_NOVEDAD (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100)
);

-- =========================
-- TABLA ESTADOS NOVEDAD
-- =========================
CREATE TABLE ESTADOS_DE_NOVEDAD (
    id_estado INT AUTO_INCREMENT PRIMARY KEY,
    nombre_estado VARCHAR(50)
);

-- =========================
-- TABLA PRIORIDADES (NUEVA)
-- =========================
CREATE TABLE PRIORIDADES (
    id_prioridad INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20)
);

-- =========================
-- TABLA NOVEDAD
-- =========================
CREATE TABLE NOVEDAD (
    id_novedad INT AUTO_INCREMENT PRIMARY KEY,
    id_inmueble INT,
    id_usuario INT,
    id_categoria INT,
    id_estado INT,
    id_prioridad INT,
    descripcion TEXT,
    fecha_reporte DATE,
    fecha_cierre DATE,

    FOREIGN KEY (id_inmueble) REFERENCES INMUEBLES(id_inmueble),
    FOREIGN KEY (id_usuario) REFERENCES USUARIOS(id_usuario),
    FOREIGN KEY (id_categoria) REFERENCES CATEGORIAS_NOVEDAD(id_categoria),
    FOREIGN KEY (id_estado) REFERENCES ESTADOS_DE_NOVEDAD(id_estado),
    FOREIGN KEY (id_prioridad) REFERENCES PRIORIDADES(id_prioridad)
);

-- =========================
-- TABLA EVIDENCIAS
-- =========================
CREATE TABLE EVIDENCIAS_DE_NOVEDAD (
    id_evidencia INT AUTO_INCREMENT PRIMARY KEY,
    id_novedad INT,
    tipo_archivo VARCHAR(50),
    url_archivo VARCHAR(200),
    fecha_subida DATE,
    subido_por VARCHAR(120),

    FOREIGN KEY (id_novedad) REFERENCES NOVEDAD(id_novedad)
);

-- =========================
-- TABLA TAREAS
-- =========================
CREATE TABLE IF NOT EXISTS tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    rol VARCHAR(50) NOT NULL,
    estado ENUM('Activo', 'Pendiente', 'Finalizado') DEFAULT 'Activo',
    prioridad ENUM('Baja', 'Media', 'Alta', 'Urgente') DEFAULT 'Media',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento DATE NOT NULL,
    INDEX idx_rol (rol),
    INDEX idx_estado (estado),
    INDEX idx_fecha_vencimiento (fecha_vencimiento)
);


-- =========================
-- TABLA PAGOS
-- =========================
CREATE TABLE PAGOS (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_inmueble INT,
    fecha_pago DATE,
    valor DECIMAL(12,2),
    estado_pago VARCHAR(50),
    metodo_pago VARCHAR(50),

    -- Campos adicionales para almacenar el registro de pago desde el formulario
    nombre VARCHAR(120),
    descripcion TEXT,
    archivo VARCHAR(200),

    FOREIGN KEY (id_inmueble) REFERENCES INMUEBLES(id_inmueble)
);

-- =========================
-- TABLA AJUSTES SALDO PENDIENTE
-- =========================
CREATE TABLE AJUSTES_SALDO_PENDIENTE (
    id_ajuste INT AUTO_INCREMENT PRIMARY KEY,
    id_inmueble INT NOT NULL,
    saldo_anterior DECIMAL(12,2) NOT NULL,
    nuevo_saldo DECIMAL(12,2) NOT NULL,
    motivo VARCHAR(255) DEFAULT 'Ajuste manual por administrador',
    id_usuario_admin INT NULL,
    fecha_ajuste DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_inmueble) REFERENCES INMUEBLES(id_inmueble),
    FOREIGN KEY (id_usuario_admin) REFERENCES USUARIOS(id_usuario)
);

-- =========================
-- TABLA DOCUMENTOS
-- =========================
CREATE TABLE DOCUMENTOS (
    id_documento INT AUTO_INCREMENT PRIMARY KEY,
    id_inmueble INT,
    tipo_documento VARCHAR(100),
    url_documento VARCHAR(200),
    fecha_subida DATE,
    visibilidad ENUM('global','inmueble') NOT NULL,
    FOREIGN KEY (id_inmueble) REFERENCES INMUEBLES(id_inmueble)
);

-- =========================
-- TABLA HISTORIAL NOVEDAD
-- =========================
CREATE TABLE HISTORIAL_DE_NOVEDAD (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_novedad INT,
    estado_anterior VARCHAR(50),
    estado_nuevo VARCHAR(50),
    id_usuario INT,
    fecha_cambio DATE,
    observacion TEXT,

    FOREIGN KEY (id_novedad) REFERENCES NOVEDAD(id_novedad),
    FOREIGN KEY (id_usuario) REFERENCES USUARIOS(id_usuario)
);

-- =========================
-- TABLA NOTIFICACIONES
-- =========================
CREATE TABLE NOTIFICACIONES (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    mensaje TEXT,
    tipo VARCHAR(50),
    leida VARCHAR(10),
    fecha_envio DATE,

    FOREIGN KEY (id_usuario) REFERENCES USUARIOS(id_usuario)
);

-- =========================
-- TABLA PQRS
-- =========================
CREATE TABLE PQRS (
    id_pqrs INT AUTO_INCREMENT PRIMARY KEY,
    id_residente INT NOT NULL,
    tipo VARCHAR(30) NOT NULL,
    asunto VARCHAR(150) NOT NULL,
    descripcion TEXT NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(30) NOT NULL DEFAULT 'Pendiente',
    FOREIGN KEY (id_residente) REFERENCES RESIDENTES(id_residente)
);

-- =========================
-- TABLA CONTACTOS
-- =========================
CREATE TABLE CONTACTOS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    correo VARCHAR(100),
    mensaje TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================
-- TABLA COMUNICACIONES
-- =========================
CREATE TABLE COMUNICACIONES (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(100),
  tipo VARCHAR(50),
  estado VARCHAR(20),
  contenido TEXT,
  fecha DATETIME,
  id_inmueble INT NULL,
  
  FOREIGN KEY (id_inmueble) REFERENCES INMUEBLES(id_inmueble)
);
-- =========================
-- Tabla Paquetes
-- =========================
CREATE TABLE paquetes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    residente VARCHAR(140) NOT NULL,
    empresa VARCHAR(140) NOT NULL,
    observaciones TEXT NOT NULL,
    estado VARCHAR(60) NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- ROLES
-- =========================
INSERT INTO ROLES (id_rol, nombre_rol) VALUES
(1,'Administrador'),
(2,'Operador'),
(3,'Residente'),
(4,'Propietario');


-- TORRES
-- =========================
INSERT INTO TORRES (nombre) VALUES
('Konoha'),
('Hoenn'),
('Midgar'),
('Raccoon'),
('Hyrule');

-- =========================
-- PRIORIDADES
-- =========================
INSERT INTO PRIORIDADES (nombre) VALUES
('Baja'),
('Media'),
('Alta'),
('Urgente');

-- =========================
-- ESTADOS NOVEDAD
-- =========================
INSERT INTO ESTADOS_DE_NOVEDAD (nombre_estado) VALUES
('Abierta'),
('En proceso'),
('Resuelta'),
('Cerrada');

-- =========================
-- CATEGORIAS NOVEDAD
-- =========================
INSERT INTO CATEGORIAS_NOVEDAD (nombre_categoria) VALUES
('Plomeria'),
('Electricidad'),
('Seguridad'),
('Ruido'),
('Ascensor'),
('Zonas comunes');

-- =========================
-- PERSONAS
-- =========================
INSERT INTO PERSONAS (nombre_completo,tipo_documento,numero_documento,telefono,correo) VALUES
('Albert Wesker','CC','1001','300000001','wesker@umbrella.com'),
('Jill Valentine','CC','1002','300000002','jill@umbrella.com'),
('Leon Kennedy','CC','1003','300000003','leon@umbrella.com'),
('Claire Redfield','CC','1004','300000004','claire@umbrella.com'),
('Monkey D Luffy','CC','1005','300000005','luffy@onepiece.com'),
('Roronoa Zoro','CC','1006','300000006','zoro@onepiece.com'),
('Nami','CC','1007','300000007','nami@onepiece.com'),
('Sanji','CC','1008','300000008','sanji@onepiece.com'),
('Tony Chopper','CC','1009','300000009','chopper@onepiece.com'),
('Gon Freecss','CC','1010','300000010','gon@hunter.com'),
('Killua Zoldyck','CC','1011','300000011','killua@hunter.com'),
('Kurapika','CC','1012','300000012','kurapika@hunter.com'),
('Hisoka','CC','1013','300000013','hisoka@hunter.com'),
('Light Yagami','CC','1014','300000014','light@deathnote.com'),
('L Lawliet','CC','1015','300000015','l@deathnote.com'),
('Ryuk','CC','1016','300000016','ryuk@deathnote.com'),
('Johnny Silverhand','CC','1017','300000017','johnny@cyberpunk.com'),
('V Mercenary','CC','1018','300000018','v@cyberpunk.com'),
('Arthur Morgan','CC','1019','300000019','arthur@rdr.com'),
('John Marston','CC','1020','300000020','john@rdr.com'),
('Cloud Strife','CC','1021','300000021','cloud@ff.com'),
('Tifa Lockhart','CC','1022','300000022','tifa@ff.com'),
('Sephiroth','CC','1023','300000023','sephiroth@ff.com'),
('Captain Price','CC','1024','300000024','price@cod.com'),
('Soap MacTavish','CC','1025','300000025','soap@cod.com'),
('Peter Parker','CC','1026','300000026','peter@spiderman.com'),
('Miles Morales','CC','1027','300000027','miles@spiderman.com'),
('Alucard','CC','1028','300000028','alucard@hellsing.com'),
('Seras Victoria','CC','1029','300000029','seras@hellsing.com'),
('Tommy Vercetti','CC','1030','300000030','tommy@gta.com'),
('CJ Johnson','CC','1031','300000031','cj@gta.com'),
('Franklin Clinton','CC','1032','300000032','franklin@gta.com'),
('Michael DeSanta','CC','1033','300000033','michael@gta.com'),
('Ada Wong','CC','1034','300000034','ada@umbrella.com'),
('Chris Redfield','CC','1035','300000035','chris@umbrella.com'),
('Brook','CC','1036','300000036','brook@onepiece.com'),
('Nico Robin','CC','1037','300000037','robin@onepiece.com'),
('Leorio','CC','1038','300000038','leorio@hunter.com'),
('Illumi','CC','1039','300000039','illumi@hunter.com'),
('Near','CC','1040','300000040','near@deathnote.com'),
('Panam Palmer','CC','1041','300000041','panam@cyberpunk.com'),
('Judy Alvarez','CC','1042','300000042','judy@cyberpunk.com'),
('Dutch Van Der Linde','CC','1043','300000043','dutch@rdr.com'),
('Sadie Adler','CC','1044','300000044','sadie@rdr.com'),
('Aerith','CC','1045','300000045','aerith@ff.com'),
('Barret','CC','1046','300000046','barret@ff.com'),
('Ghost','CC','1047','300000047','ghost@cod.com'),
('Roach','CC','1048','300000048','roach@cod.com'),
('Trevor Philips','CC','1049','300000049','trevor@gta.com');

-- =========================
-- USUARIOS
-- =========================
INSERT INTO USUARIOS (id_persona,contraseña,id_rol,estado) VALUES
(1,'admin123',1,'Activo'),
(2,'op123',2,'Activo'),
(3,'op123',2,'Activo'),
(4,'op123',2,'Activo'),
(5,'res123',3,'Activo'),
(6,'res123',3,'Activo'),
(7,'res123',3,'Activo'),
(8,'res123',3,'Activo'),
(9,'res123',3,'Activo'),
(10,'res123',3,'Activo'),
(11,'res123',3,'Activo'),
(12,'res123',3,'Activo'),
(13,'res123',3,'Activo'),
(14,'res123',3,'Activo'),
(15,'res123',3,'Activo'),
(16,'res123',3,'Activo'),
(17,'res123',3,'Activo'),
(18,'res123',3,'Activo'),
(19,'res123',3,'Activo'),
(20,'res123',3,'Activo'),
(21,'res123',3,'Activo'),
(22,'res123',3,'Activo'),
(23,'res123',3,'Activo'),
(24,'res123',3,'Activo');

-- =========================
-- EMPLEADOS
-- =========================
INSERT INTO EMPLEADOS (id_persona,cargo,especialidad,turno,estado_laboral) VALUES
(2,'Seguridad','Vigilancia','Noche','Activo'),
(3,'Mantenimiento','Electricidad','Dia','Activo'),
(4,'Operador','Administracion','Dia','Activo');

-- =========================
-- RESIDENTES
-- =========================
INSERT INTO RESIDENTES (id_persona,profesion) VALUES
(5,'Pirata'),
(6,'Espadachin'),
(7,'Navegante'),
(8,'Cocinero'),
(9,'Doctor'),
(10,'Cazador'),
(11,'Asesino'),
(12,'Detective'),
(13,'Ilusionista'),
(14,'Estudiante'),
(15,'Investigador'),
(16,'Shinigami'),
(17,'Rockstar'),
(18,'Mercenario'),
(19,'Forajido'),
(20,'Vaquero'),
(21,'Soldado'),
(22,'Barwoman'),
(23,'General'),
(24,'Capitan');

-- =========================
-- PROPIETARIOS
-- =========================
INSERT INTO PROPIETARIOS (id_persona,direccion_residencia) VALUES
(25,'Los Santos'),
(26,'New York'),
(27,'Brooklyn'),
(28,'Londres'),
(29,'Tokyo'),
(30,'Vice City'),
(31,'San Andreas'),
(32,'Los Santos'),
(33,'Los Santos'),
(34,'Raccoon City'),
(35,'Raccoon City'),
(36,'Grand Line'),
(37,'Grand Line'),
(38,'Yorknew'),
(39,'Yorknew'),
(40,'Tokyo'),
(41,'Night City'),
(42,'Night City'),
(43,'Valentine'),
(44,'Valentine'),
(45,'Midgar'),
(46,'Midgar'),
(47,'Task Force'),
(48,'Task Force'),
(49,'Los Santos');

-- =========================
-- INMUEBLES
-- =========================
INSERT INTO INMUEBLES (numero,id_torre,area,parqueadero,id_propietario) VALUES
('101',1,60,'P1',1),
('102',1,60,'P2',2),
('103',1,60,'P3',3),
('104',1,60,'P4',4),
('105',1,60,'P5',5),
('106',1,60,'P6',6),
('107',1,60,'P7',7),
('108',1,60,'P8',8),
('109',1,60,'P9',9),
('110',1,60,'P10',10),
('201',2,70,'P11',11),
('202',2,70,'P12',12),
('203',2,70,'P13',13),
('204',2,70,'P14',14),
('205',2,70,'P15',15),
('206',2,70,'P16',16),
('207',2,70,'P17',17),
('208',2,70,'P18',18),
('209',2,70,'P19',19),
('210',2,70,'P20',20),
('301',3,75,'P21',21),
('302',3,75,'P22',22),
('303',3,75,'P23',23),
('304',3,75,'P24',24),
('305',3,75,'P25',25),
('306',3,75,'P26',NULL),
('307',3,75,'P27',NULL),
('308',3,75,'P28',NULL),
('309',3,75,'P29',NULL),
('310',3,75,'P30',NULL),
('401',4,80,'P31',NULL),
('402',4,80,'P32',NULL),
('403',4,80,'P33',NULL),
('404',4,80,'P34',NULL),
('405',4,80,'P35',NULL),
('406',4,80,'P36',NULL),
('407',4,80,'P37',NULL),
('408',4,80,'P38',NULL),
('409',4,80,'P39',NULL),
('410',4,80,'P40',NULL);

-- =========================
-- RESIDENTE_INMUEBLE
-- =========================
INSERT INTO RESIDENTE_INMUEBLE VALUES
(1,1,'2024-01-01'),
(2,2,'2024-01-01'),
(3,3,'2024-01-01'),
(4,4,'2024-01-01'),
(5,5,'2024-01-01'),
(6,6,'2024-01-01'),
(7,7,'2024-01-01'),
(8,8,'2024-01-01'),
(9,9,'2024-01-01'),
(10,10,'2024-01-01'),
(11,11,'2024-01-01'),
(12,12,'2024-01-01'),
(13,13,'2024-01-01'),
(14,14,'2024-01-01'),
(15,15,'2024-01-01'),
(16,16,'2024-01-01'),
(17,17,'2024-01-01'),
(18,18,'2024-01-01'),
(19,19,'2024-01-01'),
(20,20,'2024-01-01');

-- =========================
-- MASCOTAS
-- =========================
INSERT INTO MASCOTAS (id_residente,tipo,raza,cantidad) VALUES
(1,'Perro','Akita',1),
(2,'Perro','Doberman',1),
(3,'Gato','Siames',2),
(4,'Perro','Pastor Aleman',1),
(5,'Reno','Reno',1);

-- =========================
-- CONTACTOS EMERGENCIA
-- =========================
INSERT INTO CONTACTOS_DE_EMERGENCIA (id_residente,nombre,telefono,relacion) VALUES
(1,'Chris Redfield','3110000001','Hermano'),
(2,'Rebecca Chambers','3110000002','Amiga'),
(3,'Shanks','3110000003','Capitan'),
(4,'Ivankov','3110000004','Amigo');

-- =========================
-- NOVEDADES
-- =========================
INSERT INTO NOVEDAD
(id_inmueble,id_usuario,id_categoria,id_estado,id_prioridad,descripcion,fecha_reporte,fecha_cierre)
VALUES
(1,5,1,1,3,'Fuga de agua en baño','2025-01-01',NULL),
(3,6,4,2,2,'Ruido excesivo noche','2025-02-01',NULL),
(5,7,2,3,2,'Corto electrico cocina','2025-01-15','2025-01-16');

-- =========================
-- PAGOS
-- =========================
INSERT INTO PAGOS (id_inmueble,fecha_pago,valor,estado_pago,metodo_pago) VALUES
(1,'2025-01-05',250000,'Pagado','Transferencia'),
(2,'2025-01-05',250000,'Pagado','Transferencia'),
(3,'2025-02-05',250000,'Pendiente','Efectivo');

-- =========================
-- NOTIFICACIONES
-- =========================
INSERT INTO NOTIFICACIONES (id_usuario,mensaje,tipo,leida,fecha_envio) VALUES
(5,'Su reporte fue recibido','Sistema','No','2025-01-01'),
(2,'Nueva novedad asignada','Sistema','No','2025-01-01');


-- =========================
-- DATOS DE EJEMPLO PARA TAREAS
-- =========================
INSERT INTO tareas (titulo, descripcion, rol, estado, prioridad, fecha_vencimiento) VALUES
('Revisar paquetes pendientes', 'Verificar y clasificar todos los paquetes que llegaron en el turno', 'Operador', 'Activo', 'Alta', '2026-03-27'),
('Registrar comunicación', 'Registrar la comunicación importante para los residentes del edificio', 'Operador', 'Activo', 'Media', '2026-03-28'),
('Mantenimiento de ascensor', 'Realizar revisión preventiva del ascensor principal', 'Operador', 'Pendiente', 'Urgente', '2026-03-26'),
('Limpieza de áreas comunes', 'Limpiar y organizar los pasillos del piso 3', 'Operador', 'Pendiente', 'Media', '2026-03-27'),
('Entrega de correspondencia', 'Distribuir la correspondencia en los buzones correspondientes', 'Operador', 'Finalizado', 'Baja', '2026-03-25'),
('Reporte de incidentes', 'Documentar los incidentes del día y enviar al administrador', 'Operador', 'Activo', 'Alta', '2026-03-27'),
('Verificar illuminación común', 'Revisar que las luces de áreas comunes estén funcionando', 'Operador', 'Finalizado', 'Baja', '2026-03-25');

-- =========================
-- INSERTS EXTRA 
-- =========================

INSERT INTO PERSONAS (nombre_completo,tipo_documento,numero_documento,edad,telefono,correo) VALUES
('Naruto Uzumaki','CC','2001','21','3100002001','naruto@konoha.com'),
('Sasuke Uchiha','CC','2002','22','3100002002','sasuke@konoha.com'),
('Goku Son','CC','2003','35','3100002003','goku@capsule.com'),
('Kratos Sparta','CC','2004','40','3100002004','kratos@olympus.com'),
('Link Hyrule','CC','2005','28','3100002005','link@hyrule.com'),
('Bruce Wayne','CC','2006','38','3100002006','bruce@wayneenterprises.com'),
('Patrick Bateman','CC','2007','33','3100002007','patrick@pierceandpierce.com'),
('Bilbo Bolson','CC','2008','50','3100002008','bilbo@shire.com'),
('John Kramer','CC','2009','52','3100002009','john@jigsaw.com');

INSERT INTO USUARIOS (id_persona,contraseña,id_rol,estado) VALUES
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2001' LIMIT 1),'konoha123',3,'Activo'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2002' LIMIT 1),'uchiha123',4,'Activo'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2003' LIMIT 1),'saiyan123',3,'Activo'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2004' LIMIT 1),'ares123',4,'Activo'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2005' LIMIT 1),'triforce123',3,'Activo'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2006' LIMIT 1),'bat123',4,'Activo'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2007' LIMIT 1),'wallstreet123',3,'Activo'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2008' LIMIT 1),'shire123',4,'Activo'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2009' LIMIT 1),'jigsaw123',3,'Activo');

INSERT INTO RESIDENTES (id_persona,profesion) VALUES
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2001' LIMIT 1),'Ninja'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2002' LIMIT 1),'Espadachin'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2003' LIMIT 1),'Guerrero Z'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2004' LIMIT 1),'Guerrero'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2005' LIMIT 1),'Heroe'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2006' LIMIT 1),'Empresario'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2007' LIMIT 1),'Ejecutivo'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2008' LIMIT 1),'Aventurero'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2009' LIMIT 1),'Ingeniero');

INSERT INTO PROPIETARIOS (id_persona,direccion_residencia) VALUES
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2001' LIMIT 1),'Aldea Oculta de la Hoja'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2002' LIMIT 1),'Distrito Uchiha'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2003' LIMIT 1),'Montana Paoz'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2004' LIMIT 1),'Templo de Sparta'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2005' LIMIT 1),'Castillo de Hyrule'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2006' LIMIT 1),'Mansion Wayne'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2007' LIMIT 1),'Upper East Side'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2008' LIMIT 1),'La Comarca'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2009' LIMIT 1),'Taller Kramer');

INSERT INTO EMPLEADOS (id_persona,cargo,especialidad,turno,estado_laboral) VALUES
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2001' LIMIT 1),'Vigilancia','Seguridad ninja','Noche','Activo'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2002' LIMIT 1),'Mantenimiento','Electricidad','Dia','Activo'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2003' LIMIT 1),'Conserje','Atencion residentes','Dia','Activo'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2004' LIMIT 1),'Operador','Control accesos','Noche','Activo'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2005' LIMIT 1),'Auxiliar','Documentacion','Dia','Activo'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2006' LIMIT 1),'Administrador','Gestion estrategica','Dia','Activo'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2007' LIMIT 1),'Operador','Control financiero','Dia','Activo'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2008' LIMIT 1),'Conserje','Atencion comunitaria','Dia','Activo'),
((SELECT id_persona FROM PERSONAS WHERE numero_documento='2009' LIMIT 1),'Mantenimiento','Mecanica de precision','Noche','Activo');



INSERT INTO INMUEBLES (numero,id_torre,area,parqueadero,id_propietario,total_personas,total_adultos,total_menores) VALUES
('501',1,82,'P41',1,3,2,1),
('502',2,79,'P42',2,4,3,1),
('503',3,88,'P43',3,2,2,0),
('504',4,91,'P44',4,5,3,2),
('505',1,85,'P45',5,3,2,1);

INSERT INTO RESIDENTE_INMUEBLE (id_residente,id_inmueble,fecha_ingreso) VALUES
(1,21,'2025-06-01'),
(2,22,'2025-06-02'),
(3,23,'2025-06-03'),
(4,24,'2025-06-04'),
(5,25,'2025-06-05');

INSERT INTO MASCOTAS (id_residente,tipo,raza,cantidad) VALUES
(6,'Perro','Akamaru',1),
(7,'Gato','Neko',1),
(8,'Ave','Chocobo',1),
(9,'Perro','Cerberus',1),
(10,'Dragon','Mini Dragon',1);

INSERT INTO CONTACTOS_DE_EMERGENCIA (id_residente,nombre,telefono,relacion) VALUES
(5,'Kakashi Hatake','3110002001','Tutor'),
(6,'Vegeta Prince','3110002002','Amigo'),
(7,'Tifa Lockhart','3110002003','Amiga'),
(8,'Geralt Rivia','3110002004','Aliado'),
(9,'Zelda Hyrule','3110002005','Hermana');

INSERT INTO CATEGORIAS_NOVEDAD (nombre_categoria) VALUES
('Jutsu electrico'),
('Capsula averiada'),
('Materia perdida'),
('Bug de sistema'),
('Portal inestable');

INSERT INTO ESTADOS_DE_NOVEDAD (nombre_estado) VALUES
('Reportada'),
('Asignada'),
('En revision'),
('Resuelta parcial'),
('Cerrada QA');

INSERT INTO PRIORIDADES (nombre) VALUES
('Mision S'),
('Legendaria'),
('Epic'),
('Rare'),
('Common');

INSERT INTO NOVEDAD (id_inmueble,id_usuario,id_categoria,id_estado,id_prioridad,descripcion,fecha_reporte,fecha_cierre) VALUES
(21,5,1,1,4,'Falla de energia en sala comun estilo cyberpunk','2026-01-10',NULL),
(22,6,2,2,3,'Ruido en zona social por torneo de fighting','2026-01-11',NULL),
(23,7,3,3,2,'Filtro de agua dañado en torre Midgar','2026-01-12','2026-01-15'),
(24,8,4,1,1,'Puerta principal no valida tarjetas','2026-01-13',NULL),
(25,9,5,2,4,'Iluminacion intermitente en pasillo','2026-01-14',NULL);

INSERT INTO EVIDENCIAS_DE_NOVEDAD (id_novedad,tipo_archivo,url_archivo,fecha_subida,subido_por) VALUES
(1,'imagen','uploads/evidencias/naruto_01.jpg','2026-01-10','Naruto Uzumaki'),
(2,'video','uploads/evidencias/goku_02.mp4','2026-01-11','Goku Son'),
(3,'pdf','uploads/evidencias/kratos_03.pdf','2026-01-12','Kratos Sparta'),
(1,'imagen','uploads/evidencias/link_04.jpg','2026-01-13','Link Hyrule'),
(2,'audio','uploads/evidencias/sasuke_05.mp3','2026-01-14','Sasuke Uchiha');

INSERT INTO tareas (titulo, descripcion, rol, estado, prioridad, fecha_vencimiento) VALUES
('Patrullar torre Konoha','Revisar accesos y camaras en niveles 1-5','Operador','Activo','Alta','2026-04-01'),
('Registrar pagos Midgar','Validar soportes de pago de torre Midgar','Operador','Pendiente','Media','2026-04-02'),
('Auditar documentos Hyrule','Verificar vigencia de documentos cargados','Operador','Activo','Urgente','2026-04-03'),
('Inspeccion ascensor Rapture','Revisar estado mecanico y reporte tecnico','Operador','Pendiente','Alta','2026-04-04'),
('Cerrar casos backlog','Finalizar tareas antiguas pendientes','Operador','Finalizado','Baja','2026-03-30');

INSERT INTO PAGOS (id_inmueble,fecha_pago,valor,estado_pago,metodo_pago,nombre,descripcion,archivo) VALUES
(21,'2026-01-05',320000,'Pagado','Transferencia','Naruto Uzumaki','Cuota administracion enero','uploads/pagos/pago_naruto.pdf'),
(22,'2026-01-06',300000,'Pendiente','Efectivo','Sasuke Uchiha','Cuota administracion enero','uploads/pagos/pago_sasuke.pdf'),
(23,'2026-01-07',280000,'Pagado','Tarjeta','Goku Son','Cuota administracion enero','uploads/pagos/pago_goku.pdf'),
(24,'2026-01-08',350000,'Pendiente','Transferencia','Kratos Sparta','Cuota administracion enero','uploads/pagos/pago_kratos.pdf'),
(25,'2026-01-09',295000,'Pagado','PSE','Link Hyrule','Cuota administracion enero','uploads/pagos/pago_link.pdf');

INSERT INTO AJUSTES_SALDO_PENDIENTE (id_inmueble,saldo_anterior,nuevo_saldo,motivo,id_usuario_admin) VALUES
(21,320000,280000,'Descuento evento Konoha',1),
(22,300000,300000,'Sin cambios por validacion',1),
(23,280000,250000,'Ajuste por pronto pago',1),
(24,350000,360000,'Recargo mora',1),
(25,295000,270000,'Bonificacion comunitaria',1);

INSERT INTO DOCUMENTOS (id_inmueble,tipo_documento,url_documento,fecha_subida,visibilidad) VALUES
(21,'Manual convivencia','uploads/documentos/manual_konoha.pdf','2026-01-01','inmueble'),
(22,'Reglamento pagos','uploads/documentos/pagos_namek.pdf','2026-01-02','inmueble'),
(23,'Acta reunion','uploads/documentos/acta_midgar.pdf','2026-01-03','global'),
(24,'Circular seguridad','uploads/documentos/seguridad_rapture.pdf','2026-01-04','global'),
(25,'Guia residentes','uploads/documentos/guia_hyrule.pdf','2026-01-05','inmueble');

INSERT INTO HISTORIAL_DE_NOVEDAD (id_novedad,estado_anterior,estado_nuevo,id_usuario,fecha_cambio,observacion) VALUES
(1,'Abierta','En proceso',2,'2026-01-10','Asignada a operador ninja'),
(2,'En proceso','Resuelta',3,'2026-01-11','Se redujo nivel de ruido'),
(3,'Abierta','Cerrada',4,'2026-01-12','Caso resuelto con mantenimiento'),
(1,'En proceso','Resuelta',2,'2026-01-13','Validado por administrador'),
(2,'Resuelta','Cerrada',1,'2026-01-14','Cierre definitivo');

INSERT INTO NOTIFICACIONES (id_usuario,mensaje,tipo,leida,fecha_envio) VALUES
(5,'Nueva tarea asignada en torre Konoha','Sistema','No','2026-01-10'),
(6,'Pago pendiente detectado en inmueble 22','Finanzas','No','2026-01-11'),
(7,'Novedad actualizada a En proceso','Novedades','Si','2026-01-12'),
(8,'Documento nuevo disponible','Documentos','No','2026-01-13'),
(9,'Recordatorio de cuota de administracion','Finanzas','No','2026-01-14');

INSERT INTO CONTACTOS (nombre,correo,mensaje) VALUES
('Ichigo Kurosaki','ichigo@bleach.com','Solicitud de informacion sobre reglamento interno'),
('Aloy Nora','aloy@horizon.com','Consulta por estado de comunicados'),
('Jin Kazama','jin@tekken.com','Reporte de ruido en zona comun'),
('Makoto Yuki','makoto@persona.com','Peticion de soporte para acceso'),
('Samus Aran','samus@metroid.com','Consulta sobre pagos pendientes');

INSERT INTO COMUNICACIONES (titulo,tipo,estado,contenido,fecha,id_inmueble) VALUES
('Evento Konoha Matsuri','Comunidad','Activo','Invitacion a actividad cultural anime','2026-01-15 08:00:00',21),
('Torneo Smash vecinos','Recreacion','Activo','Inscripciones abiertas para torneo','2026-01-16 09:00:00',22),
('Mantenimiento Midgar','Informativo','Cerrado','Se realizo mantenimiento general','2026-01-17 10:00:00',23),
('Campana seguridad Rapture','Seguridad','Activo','Recomendaciones de acceso seguro','2026-01-18 11:00:00',24),
('Asamblea Hyrule','Administrativo','Programado','Reunion de propietarios y residentes','2026-01-19 18:00:00',25);

INSERT INTO paquetes (residente,empresa,observaciones,estado) VALUES
('Naruto Uzumaki','Konoha Express','Caja de pergaminos','Pendiente'),
('Sasuke Uchiha','Uchiha Logistics','Paquete fragil','Entregado'),
('Goku Son','Capsule Corp Courier','Repuestos de entrenamiento','Pendiente'),
('Kratos Sparta','Olympus Delivery','Equipo deportivo','Devuelto'),
('Link Hyrule','Hylian Post','Encomienda especial','Entregado');



INSERT INTO INMUEBLES (numero,id_torre,area,parqueadero,id_propietario,total_personas,total_adultos,total_menores) VALUES
('506',2,92,'P46',(SELECT pr.id_propietario FROM PROPIETARIOS pr INNER JOIN PERSONAS p ON pr.id_persona = p.id_persona WHERE p.numero_documento='2006' LIMIT 1),2,2,0),
('507',3,77,'P47',(SELECT pr.id_propietario FROM PROPIETARIOS pr INNER JOIN PERSONAS p ON pr.id_persona = p.id_persona WHERE p.numero_documento='2007' LIMIT 1),1,1,0),
('508',4,68,'P48',(SELECT pr.id_propietario FROM PROPIETARIOS pr INNER JOIN PERSONAS p ON pr.id_persona = p.id_persona WHERE p.numero_documento='2008' LIMIT 1),3,2,1),
('509',1,86,'P49',(SELECT pr.id_propietario FROM PROPIETARIOS pr INNER JOIN PERSONAS p ON pr.id_persona = p.id_persona WHERE p.numero_documento='2009' LIMIT 1),2,2,0);

INSERT INTO RESIDENTE_INMUEBLE (id_residente,id_inmueble,fecha_ingreso) VALUES
((SELECT r.id_residente FROM RESIDENTES r INNER JOIN PERSONAS p ON r.id_persona = p.id_persona WHERE p.numero_documento='2006' LIMIT 1),(SELECT id_inmueble FROM INMUEBLES WHERE numero='506' LIMIT 1),'2026-02-01'),
((SELECT r.id_residente FROM RESIDENTES r INNER JOIN PERSONAS p ON r.id_persona = p.id_persona WHERE p.numero_documento='2007' LIMIT 1),(SELECT id_inmueble FROM INMUEBLES WHERE numero='507' LIMIT 1),'2026-02-02'),
((SELECT r.id_residente FROM RESIDENTES r INNER JOIN PERSONAS p ON r.id_persona = p.id_persona WHERE p.numero_documento='2008' LIMIT 1),(SELECT id_inmueble FROM INMUEBLES WHERE numero='508' LIMIT 1),'2026-02-03'),
((SELECT r.id_residente FROM RESIDENTES r INNER JOIN PERSONAS p ON r.id_persona = p.id_persona WHERE p.numero_documento='2009' LIMIT 1),(SELECT id_inmueble FROM INMUEBLES WHERE numero='509' LIMIT 1),'2026-02-04');

INSERT INTO MASCOTAS (id_residente,tipo,raza,cantidad) VALUES
((SELECT r.id_residente FROM RESIDENTES r INNER JOIN PERSONAS p ON r.id_persona = p.id_persona WHERE p.numero_documento='2006' LIMIT 1),'Perro','Doberman',1),
((SELECT r.id_residente FROM RESIDENTES r INNER JOIN PERSONAS p ON r.id_persona = p.id_persona WHERE p.numero_documento='2007' LIMIT 1),'Gato','Persa',1),
((SELECT r.id_residente FROM RESIDENTES r INNER JOIN PERSONAS p ON r.id_persona = p.id_persona WHERE p.numero_documento='2008' LIMIT 1),'Perro','Lobero',1),
((SELECT r.id_residente FROM RESIDENTES r INNER JOIN PERSONAS p ON r.id_persona = p.id_persona WHERE p.numero_documento='2009' LIMIT 1),'Ave','Cuervo',1);

INSERT INTO CONTACTOS_DE_EMERGENCIA (id_residente,nombre,telefono,relacion) VALUES
((SELECT r.id_residente FROM RESIDENTES r INNER JOIN PERSONAS p ON r.id_persona = p.id_persona WHERE p.numero_documento='2006' LIMIT 1),'Alfred Pennyworth','3111002006','Mayordomo'),
((SELECT r.id_residente FROM RESIDENTES r INNER JOIN PERSONAS p ON r.id_persona = p.id_persona WHERE p.numero_documento='2007' LIMIT 1),'Evelyn Williams','3111002007','Companera'),
((SELECT r.id_residente FROM RESIDENTES r INNER JOIN PERSONAS p ON r.id_persona = p.id_persona WHERE p.numero_documento='2008' LIMIT 1),'Gandalf Grey','3111002008','Amigo'),
((SELECT r.id_residente FROM RESIDENTES r INNER JOIN PERSONAS p ON r.id_persona = p.id_persona WHERE p.numero_documento='2009' LIMIT 1),'Amanda Young','3111002009','Asistente');

INSERT INTO NOVEDAD (id_inmueble,id_usuario,id_categoria,id_estado,id_prioridad,descripcion,fecha_reporte,fecha_cierre) VALUES
((SELECT id_inmueble FROM INMUEBLES WHERE numero='506' LIMIT 1),(SELECT u.id_usuario FROM USUARIOS u INNER JOIN PERSONAS p ON u.id_persona = p.id_persona WHERE p.numero_documento='2006' LIMIT 1),1,2,3,'Reparacion de camaras en ala oeste','2026-02-10',NULL),
((SELECT id_inmueble FROM INMUEBLES WHERE numero='507' LIMIT 1),(SELECT u.id_usuario FROM USUARIOS u INNER JOIN PERSONAS p ON u.id_persona = p.id_persona WHERE p.numero_documento='2007' LIMIT 1),2,1,2,'Control de acceso en ascensor principal','2026-02-11',NULL),
((SELECT id_inmueble FROM INMUEBLES WHERE numero='508' LIMIT 1),(SELECT u.id_usuario FROM USUARIOS u INNER JOIN PERSONAS p ON u.id_persona = p.id_persona WHERE p.numero_documento='2008' LIMIT 1),3,3,4,'Revision de tuberia en cocina','2026-02-12','2026-02-13'),
((SELECT id_inmueble FROM INMUEBLES WHERE numero='509' LIMIT 1),(SELECT u.id_usuario FROM USUARIOS u INNER JOIN PERSONAS p ON u.id_persona = p.id_persona WHERE p.numero_documento='2009' LIMIT 1),4,2,1,'Falla intermitente en cerradura digital','2026-02-13',NULL);

INSERT INTO PAGOS (id_inmueble,fecha_pago,valor,estado_pago,metodo_pago,nombre,descripcion,archivo) VALUES
((SELECT id_inmueble FROM INMUEBLES WHERE numero='506' LIMIT 1),'2026-02-05',410000,'Pagado','Transferencia','Bruce Wayne','Cuota administracion febrero','uploads/pagos/pago_bruce.pdf'),
((SELECT id_inmueble FROM INMUEBLES WHERE numero='507' LIMIT 1),'2026-02-06',305000,'Pendiente','Tarjeta','Patrick Bateman','Cuota administracion febrero','uploads/pagos/pago_patrick.pdf'),
((SELECT id_inmueble FROM INMUEBLES WHERE numero='508' LIMIT 1),'2026-02-07',275000,'Pagado','PSE','Bilbo Bolson','Cuota administracion febrero','uploads/pagos/pago_bilbo.pdf'),
((SELECT id_inmueble FROM INMUEBLES WHERE numero='509' LIMIT 1),'2026-02-08',360000,'Pendiente','Efectivo','John Kramer','Cuota administracion febrero','uploads/pagos/pago_john.pdf');

INSERT INTO AJUSTES_SALDO_PENDIENTE (id_inmueble,saldo_anterior,nuevo_saldo,motivo,id_usuario_admin) VALUES
((SELECT id_inmueble FROM INMUEBLES WHERE numero='506' LIMIT 1),410000,390000,'Descuento por pronto pago Wayne',1),
((SELECT id_inmueble FROM INMUEBLES WHERE numero='507' LIMIT 1),305000,320000,'Recargo por mora Bateman',1),
((SELECT id_inmueble FROM INMUEBLES WHERE numero='508' LIMIT 1),275000,260000,'Ajuste de fidelizacion Bolson',1),
((SELECT id_inmueble FROM INMUEBLES WHERE numero='509' LIMIT 1),360000,360000,'Saldo confirmado Kramer',1);

INSERT INTO DOCUMENTOS (id_inmueble,tipo_documento,url_documento,fecha_subida,visibilidad) VALUES
((SELECT id_inmueble FROM INMUEBLES WHERE numero='506' LIMIT 1),'Reglamento interno','uploads/documentos/wayne_reglamento.pdf','2026-02-01','inmueble'),
((SELECT id_inmueble FROM INMUEBLES WHERE numero='507' LIMIT 1),'Circular convivencia','uploads/documentos/bateman_circular.pdf','2026-02-02','inmueble'),
((SELECT id_inmueble FROM INMUEBLES WHERE numero='508' LIMIT 1),'Acta comite','uploads/documentos/bolson_acta.pdf','2026-02-03','global'),
((SELECT id_inmueble FROM INMUEBLES WHERE numero='509' LIMIT 1),'Aviso mantenimiento','uploads/documentos/kramer_mantenimiento.pdf','2026-02-04','inmueble');

INSERT INTO NOTIFICACIONES (id_usuario,mensaje,tipo,leida,fecha_envio) VALUES
((SELECT u.id_usuario FROM USUARIOS u INNER JOIN PERSONAS p ON u.id_persona = p.id_persona WHERE p.numero_documento='2006' LIMIT 1),'Tu pago fue aplicado correctamente','Finanzas','No','2026-02-09'),
((SELECT u.id_usuario FROM USUARIOS u INNER JOIN PERSONAS p ON u.id_persona = p.id_persona WHERE p.numero_documento='2007' LIMIT 1),'Tienes un ajuste de saldo pendiente','Finanzas','No','2026-02-10'),
((SELECT u.id_usuario FROM USUARIOS u INNER JOIN PERSONAS p ON u.id_persona = p.id_persona WHERE p.numero_documento='2008' LIMIT 1),'Nueva comunicacion de la administracion','Sistema','No','2026-02-11'),
((SELECT u.id_usuario FROM USUARIOS u INNER JOIN PERSONAS p ON u.id_persona = p.id_persona WHERE p.numero_documento='2009' LIMIT 1),'Novedad actualizada a En proceso','Novedades','No','2026-02-12');

INSERT INTO COMUNICACIONES (titulo,tipo,estado,contenido,fecha,id_inmueble) VALUES
('Aviso mansion Wayne','Seguridad','Activo','Nuevo protocolo de ingreso de visitantes','2026-02-10 08:30:00',(SELECT id_inmueble FROM INMUEBLES WHERE numero='506' LIMIT 1)),
('Circular torre Bateman','Administrativo','Activo','Recordatorio de pagos y fechas limite','2026-02-11 09:00:00',(SELECT id_inmueble FROM INMUEBLES WHERE numero='507' LIMIT 1)),
('Comunicado torre Bolson','Comunidad','Programado','Jornada de integracion de residentes','2026-02-12 17:00:00',(SELECT id_inmueble FROM INMUEBLES WHERE numero='508' LIMIT 1)),
('Mantenimiento Kramer','Informativo','Activo','Intervencion tecnica de cerraduras','2026-02-13 10:15:00',(SELECT id_inmueble FROM INMUEBLES WHERE numero='509' LIMIT 1));

INSERT INTO paquetes (residente,empresa,observaciones,estado) VALUES
('Bruce Wayne','Gotham Logistics','Paquete confidencial','Pendiente'),
('Patrick Bateman','WallStreet Express','Documento financiero','Entregado'),
('Bilbo Bolson','Shire Mail','Caja de libros antiguos','Pendiente'),
('John Kramer','Jigsaw Couriers','Herramientas de precision','Devuelto');

