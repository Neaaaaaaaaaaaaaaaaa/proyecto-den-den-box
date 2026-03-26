
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

    ALTER TABLE PERSONAS ADD edad INT;

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
    profesion VARCHAR(100),

    FOREIGN KEY (id_persona) REFERENCES PERSONAS(id_persona)
);

    ALTER TABLE RESIDENTES MODIFY profesion VARCHAR(100) NULL;

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
('Alpha'),
('Bravo'),
('Charlie'),
('Delta');

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

