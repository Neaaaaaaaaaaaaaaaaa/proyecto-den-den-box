# Manual Tecnico del Software - Den Den Box

Version: 1.3  
Fecha: 2026-03-30  
Proyecto: Den Den Box - Sistema de gestion residencial

Fuente tecnica: elaborado sobre el codigo del repositorio proyecto-den-den-box (rama develop), revisando estructura HTML/PHP, scripts en programas y esquema en BD.sql.

## 1. Indice
1. Indice  
2. Introduccion  
3. Objetivos del Sistema  
4. Requisitos del Sistema  
5. Arquitectura del Software  
6. Instalacion y Configuracion  
7. Descripcion de Modulos y Funciones  
8. Interfaz de Usuario  
9. APIs y Servicios Externos  
10. Seguridad  
11. Pruebas y Depuracion  
12. Mantenimiento y Actualizaciones  
13. Resolucion de Problemas  
14. Anexos

## 2. Introduccion
Este manual tecnico documenta la estructura y operacion interna de Den Den Box para facilitar instalacion, soporte, mantenimiento y evolucion del software.

## 3. Objetivos del Sistema
El sistema busca:

- Centralizar la gestion de usuarios y roles del conjunto residencial.
- Controlar comunicaciones, documentos, pagos, PQRS y tareas.
- Trazar la operacion por inmueble y por perfil de usuario.

## 4. Requisitos del Sistema
Infraestructura minima sugerida:

- PHP 8.0 o superior.
- MySQL 5.7+ o MariaDB 10.4+.
- Apache 2.4+.
- XAMPP en Windows.

Permisos requeridos:

- Escritura en documentos/.
- Escritura en uploads/.
- Escritura en programas/uploads/comunicaciones/.

## 5. Arquitectura del Software
Arquitectura general en tres capas:

1. Presentacion: vistas HTML en html/.
2. Logica: scripts PHP en programas/.
3. Datos: esquema MySQL definido en BD.sql.

Componentes principales:

- Autenticacion y sesiones.
- Gestion administrativa.
- Operacion de operador, residente y propietario.
- Gestion de archivos en documentos/ y uploads/.

## 6. Instalacion y Configuracion
Instalacion local:

1. Copiar proyecto a c:/xampp/htdocs/proyecto-den-den-box.
2. Iniciar Apache y MySQL en XAMPP.
3. Importar BD.sql.
4. Verificar conexion en programas/comun/conexion.php.
5. Probar acceso en http://localhost/proyecto-den-den-box/html/comun/index.html.

Configuracion de BD (conexion.php):

- Host: localhost.
- User: root.
- Password: vacio.
- DB: sistema_gestion_novedades.

## 7. Descripcion de Modulos y Funciones
Modulo autenticacion:

- programas/auth/login.php.
- programas/auth/logout.php.
- programas/auth/recuperar_contrasena.php.

Modulo administrador:

- programas/admin/guardar_usuario.php.
- programas/admin/gestion_usuarios.php.
- programas/admin/subir_documento.php.
- programas/admin/guardar_comunicacion.php.
- programas/admin/admin_kpis.php.

Modulo operador:

- programas/operador/guardar_paquetes.php.
- programas/operador/listar_tareas_operador.php.

Modulo residente:

- programas/residente/guardar_pqrs.php.
- programas/residente/subir_pago.php.
- programas/residente/listar_documentos.php.

Modulo propietario:

- programas/propietario/guardar_usuario_propietario.php.
- programas/propietario/listar_novedades_propietario.php.

## 8. Interfaz de Usuario
Vistas principales por rol:

- Administrador: html/admin/index_admin.html y html/admin/admin_dashboard.html.
- Operador: html/operador/index_operador.html y html/operador/operator_dashboard.html.
- Residente: html/residente/index_residente.html y html/residente/user_dashboard.html.
- Propietario: html/propietario/index_propietario.html y html/propietario/propietario_crear.php.

Caracteristicas de interfaz:

- Formularios POST.
- Tablas de listado y consulta.
- Navegacion por menu y enlaces.

## 9. APIs y Servicios Externos
El sistema no expone API REST publica formal. La operacion se realiza por endpoints PHP internos.

Servicios externos detectados:

- No se identifican integraciones obligatorias con terceros en el flujo base.
- Carga y gestion de archivos local en servidor web.

## 10. Seguridad
Riesgos tecnicos detectados:

- SQL por concatenacion en varios scripts.
- Contrasenas en texto plano.
- Control de sesion no uniforme en todos los endpoints.
- DDL en tiempo de ejecucion en algunos flujos.

Recomendaciones:

1. Migrar contrasenas a password_hash/password_verify.
2. Aplicar prepared statements.
3. Unificar middleware de autenticacion y autorizacion.
4. Separar migraciones de base de datos del flujo de negocio.

## 11. Pruebas y Depuracion
Pruebas funcionales recomendadas:

1. Login valido/invalido por rol.
2. Alta/edicion/inactivacion de usuario.
3. Publicacion y consulta de comunicaciones/documentos.
4. Registro de PQRS, pagos y paquetes.
5. Creacion de residentes por propietario.

Depuracion:

- Revisar errores de PHP/mysqli.
- Validar variables de sesion.
- Revisar integridad referencial en operaciones CRUD.

## 12. Mantenimiento y Actualizaciones
Lineamientos:

- Backup diario de base de datos.
- Backup periodico de documentos y uploads.
- Registro de cambios por version.

Comandos de referencia:

- mysqldump -u root -p sistema_gestion_novedades > backup_den_den_box.sql
- mysql -u root -p sistema_gestion_novedades < backup_den_den_box.sql

## 13. Resolucion de Problemas
Incidencias comunes:

1. Error de conexion a base de datos.
2. Login sin redireccion.
3. Error en carga de archivos.
4. Modulos vacios por asociacion incorrecta de inmueble.

## 14. Anexos
Rutas clave del sistema:

- html/comun/login.html.
- programas/comun/conexion.php.
- programas/auth/login.php.
- programas/admin/guardar_usuario.php.
- programas/propietario/guardar_usuario_propietario.php.

Glosario tecnico:

- Rol: perfil de acceso con permisos definidos.
- Endpoint: script que procesa una operacion del sistema.
- DDL: sentencias de estructura de base de datos.
- KPI: indicador de desempeno operativo.

Referencias:

- Guia para la elaboracion del manual tecnico y de operacion del sistema (DNP).
- Modelo institucional en PDF suministrado en este proyecto.

Control de cambios:

- v1.3 (2026-03-30): ajuste de nombres de secciones segun contenido del PDF.
- v1.2 (2026-03-30): unificacion inicial entre manuales.
- v1.1 (2026-03-30): ajuste al formato institucional.

Fin del documento.
