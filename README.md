# Den Den Box

Sistema de Gestión Residencial

---

## 1. Descripción del Proyecto

Den Den Box es una plataforma web orientada a la gestión integral de conjuntos residenciales. El sistema permite administrar usuarios, comunicaciones, documentos, pagos, PQRS y tareas operativas, organizando la información por roles e inmuebles.

Su propósito es centralizar los procesos administrativos y operativos, facilitando la interacción entre administradores, operadores, residentes y propietarios.

---

## 2. Objetivos

* Centralizar la gestión de usuarios y roles.
* Administrar comunicaciones y documentos.
* Registrar PQRS, pagos y paquetes.
* Mejorar la trazabilidad de la operación por inmueble.
* Facilitar la interacción entre los diferentes actores del sistema.

---

## 3. Roles del Sistema

El sistema contempla los siguientes roles:

* Administrador
* Operador
* Residente
* Propietario

Cada rol cuenta con funcionalidades y vistas específicas dentro del sistema.

---

## 4. Tecnologías Utilizadas

* Backend: PHP 8 o superior
* Base de datos: MySQL 5.7+ o MariaDB 10.4+
* Servidor: Apache 2.4+ (XAMPP recomendado)
* Frontend: HTML y CSS
* Arquitectura: Modelo de tres capas (presentación, lógica y datos)

---

## 5. Arquitectura del Sistema

El sistema está estructurado bajo una arquitectura de tres capas:

* Capa de presentación: archivos HTML ubicados en la carpeta `/html`
* Capa lógica: scripts PHP ubicados en la carpeta `/programas`
* Capa de datos: base de datos MySQL definida en el archivo `BD.sql`

---

## 6. Instalación y Configuración

### 6.1 Instalación en entorno local

1. Copiar el proyecto en la ruta:
   C:\xampp\htdocs\proyecto-den-den-box

2. Iniciar los servicios Apache y MySQL desde XAMPP.

3. Importar la base de datos utilizando el archivo `BD.sql`.

4. Configurar la conexión a la base de datos en:
   `programas/comun/conexion.php`

   Parámetros de conexión:

   * Host: localhost
   * Usuario: root
   * Contraseña: (vacía)
   * Base de datos: sistema_gestion_novedades

5. Acceder al sistema desde el navegador en la URL:
   http://localhost/proyecto-den-den-box/html/comun/index.html

---

## 7. Uso del Sistema (Guía Básica)

1. Abrir un navegador web actualizado (Chrome, Edge o Firefox).
2. Ingresar a la URL del sistema.
3. Iniciar sesión con las credenciales asignadas.
4. Acceder al panel correspondiente según el rol del usuario.

### 7.1 Funcionalidades principales por rol

Administrador:

* Gestión de usuarios
* Publicación de comunicaciones
* Carga de documentos
* Consulta de indicadores (KPI)

Operador:

* Registro de paquetes
* Gestión de tareas

Residente:

* Registro de PQRS
* Registro de pagos con soporte
* Consulta de documentos y comunicaciones

Propietario:

* Consulta de información de inmuebles
* Creación de residentes asociados

---

## 8. Estructura del Proyecto

proyecto-den-den-box/

* html/                Vistas del sistema
* programas/           Lógica del sistema en PHP

  * auth/
  * admin/
  * operador/
  * residente/
  * propietario/
  * comun/
* documentos/          Archivos del sistema
* uploads/             Archivos cargados por usuarios
* BD.sql               Script de base de datos
* README.md            Documento descriptivo

---

## 9. Seguridad

### 9.1 Riesgos identificados

* Uso de consultas SQL sin protección adecuada.
* Manejo de contraseñas en texto plano.
* Control de sesiones no uniforme.

### 9.2 Recomendaciones

* Implementar funciones de cifrado como password_hash y password_verify.
* Utilizar consultas preparadas (Prepared Statements).
* Centralizar la autenticación y autorización.
* Separar la lógica de base de datos del flujo de negocio.

---

## 10. Pruebas del Sistema

Se recomienda realizar las siguientes pruebas funcionales:

* Validación de inicio de sesión por rol
* Gestión de usuarios (creación, edición e inactivación)
* Registro y consulta de PQRS, pagos y paquetes
* Publicación y consulta de documentos y comunicaciones

---

## 11. Mantenimiento

* Realizar copias de seguridad diarias de la base de datos mediante:
  mysqldump -u root -p sistema_gestion_novedades > backup.sql

* Restaurar la base de datos con:
  mysql -u root -p sistema_gestion_novedades < backup.sql

* Ejecutar copias periódicas de las carpetas:
  documentos/
  uploads/

---

## 12. Problemas Comunes

* Error de conexión a la base de datos
* Credenciales incorrectas
* Fallos en la carga de archivos
* Módulos sin información debido a configuración incorrecta

---

## 13. Documentación

La documentación completa del sistema se encuentra en:

* Manual técnico: docs/manual_tecnico_den_den_box.pdf
* Manual de usuario: docs/manual_usuario_den_den_box.pdf

---

## 14. Glosario

* PQRS: Peticiones, Quejas, Reclamos y Sugerencias
* Rol: Perfil de acceso con permisos definidos
* KPI: Indicador clave de desempeño
* Endpoint: Script que ejecuta una funcionalidad del sistema

---

## 15. Información del Proyecto

Versión: 1.4
Fecha: 30 de marzo de 2026

Proyecto desarrollado como parte del proceso formativo en análisis y desarrollo de software.

---

## 16. Soporte

Para reportar incidencias, se debe incluir:

* Nombre del usuario
* Rol
* Módulo donde ocurre la falla
* Fecha y hora
* Evidencia (captura de pantalla)

---

Fin del documento
