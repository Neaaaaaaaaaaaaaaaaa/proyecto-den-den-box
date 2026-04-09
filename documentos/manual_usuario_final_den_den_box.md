# Manual de Usuario de Software - Den Den Box

Version: 1.4  
Fecha: 2026-03-30  
Proyecto: Den Den Box

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
Este manual de usuario explica de forma clara como operar Den Den Box por rol, incluyendo acceso, navegacion y procesos frecuentes.

## 3. Objetivos del Sistema
El sistema permite:

- Gestionar usuarios y roles del conjunto residencial.
- Registrar y consultar comunicaciones.
- Cargar y consultar documentos por visibilidad.
- Registrar PQRS, pagos y paquetes segun el rol.

## 4. Requisitos del Sistema
Hardware recomendado:

- RAM minima: 4 GB.
- Procesador: doble nucleo o superior.
- Disco duro: 1 GB libre para navegador y descargas.

Software necesario:

- Sistema operativo: Windows 10 o superior.
- Navegador: Chrome, Edge o Firefox actualizado.
- Conectividad a la URL del sistema.

[ESPACIO PARA CAPTURA - Verificacion de navegador y acceso]

## 5. Arquitectura del Software
Desde la perspectiva del usuario, Den Den Box funciona como plataforma web por roles:

- Administrador.
- Operador.
- Residente.
- Propietario.

Cada rol tiene menu, panel y funciones propias.

## 6. Instalacion y Configuracion
Para usuario final no se requiere instalacion local del sistema; se accede por navegador.

Pasos de acceso:

1. Abrir navegador.
2. Ingresar a la URL del proyecto.
3. Entrar al formulario de inicio de sesion.

Ruta principal: html/comun/login.html

[ESPACIO PARA CAPTURA - Pagina de inicio]
[ESPACIO PARA CAPTURA - Formulario login]

## 7. Descripcion de Modulos y Funciones
Administrador:

- Crear usuario.
- Gestionar usuarios.
- Publicar comunicaciones.
- Subir documentos.
- Consultar reportes y KPI.

Operador:

- Registrar paquetes.
- Consultar y actualizar tareas.

Residente:

- Registrar PQRS.
- Registrar pagos con soporte.
- Consultar comunicaciones.
- Consultar documentos.

Propietario:

- Consultar comunicaciones de sus inmuebles.
- Consultar documentos de sus inmuebles.
- Crear residentes para inmuebles propios.

[ESPACIO PARA CAPTURA - Menu de modulos por rol]

## 8. Interfaz de Usuario
Elementos comunes de interfaz:

- Barra de navegacion superior.
- Menu lateral o accesos directos.
- Formularios de registro.
- Tablas de consulta.
- Boton de cierre de sesion.

[ESPACIO PARA CAPTURA - Interfaz general del sistema]
[ESPACIO PARA CAPTURA - Redireccion por rol]

## 9. APIs y Servicios Externos
Para usuario final no se consumen APIs de forma directa. El sistema opera mediante formularios y pantallas web internas.

No se requiere configuracion de servicios externos para el uso funcional diario.

## 10. Seguridad
Buenas practicas de uso:

- No compartir credenciales.
- Cerrar sesion al terminar.
- Verificar datos antes de guardar formularios.
- Reportar accesos no autorizados.

Buenas practicas para capturas:

- Usar capturas completas con fecha y modulo visible.
- Guardar evidencia de exito y de error.

## 11. Pruebas y Depuracion
Validaciones funcionales sugeridas:

1. Login correcto e incorrecto.
2. Recuperacion de contrasena.
3. Registro de formularios por rol.
4. Consulta de documentos/comunicaciones.

[ESPACIO PARA CAPTURA - Caso practico principal]

## 12. Mantenimiento y Actualizaciones
Cuando exista una nueva version, se debe informar al usuario:

- Fecha de actualizacion.
- Modulo impactado.
- Cambio funcional.
- Accion esperada del usuario.

[ESPACIO PARA CAPTURA - Registro de cambios del sistema]

## 13. Resolucion de Problemas
Errores comunes:

1. Usuario o contrasena incorrectos.
2. Sesion expirada o acceso no autorizado.
3. Error al subir archivo.
4. Modulos sin informacion visible.

[ESPACIO PARA CAPTURA - Mensaje de error comun]

## 14. Anexos
Soporte tecnico (datos para reporte):

1. Nombre del usuario.
2. Rol.
3. Modulo donde ocurre la falla.
4. Fecha y hora.
5. Captura de pantalla.

[ESPACIO PARA CAPTURA - Evidencia para soporte]

Glosario:

- PQRS: peticiones, quejas, reclamos y sugerencias.
- Novedad: incidente reportado para gestion.
- Comunicacion global: visible para todos los usuarios.
- Comunicacion por inmueble: visible por perfil e inmueble.
- Morosidad: cartera pendiente de pago.

Referencias:

- Modelo institucional de manual de usuario suministrado en el proyecto.
- Guia general de documentacion tecnica y de usuario usada como base metodologica.

Control de cambios:

- v1.4 (2026-03-30): estructura y nombres ajustados al contenido del PDF.
- v1.3 (2026-03-30): unificacion inicial de secciones entre manuales.
- v1.2 (2026-03-30): estructura tutorial con espacios de captura.

Fin del documento.
