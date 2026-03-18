<?php

include("conexion.php");

if(!$conexion){
    die("Error: no hay conexión con la base de datos");
}

/* =========================
   CAPTURA DE DATOS
========================= */

$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$contrasena = $_POST['contrasena'];
$rol = 3;

$documento = $_POST['documento'];
$num_doc = $_POST['num_doc'];
$num_tlf = $_POST['num_tlf'];
$edad = isset($_POST['edad']) ? intval($_POST['edad']) : 0;
$profesion = $_POST['profesion'];

$num_inmueble = $_POST['num_inmueble'];
$num_torre = $_POST['num_torre'];
$area = $_POST['area'];
$parqueadero = $_POST['parqueadero'];

$nombre_emergencia = $_POST['nombre_emergencia'];
$num_tlf_emergencia = $_POST['num_tlf_emergencia'];
$relacion = $_POST['relacion'];

$tipo_mascota = $_POST['tipo_mascota'];
$raza = $_POST['raza'];
$cantidad_mascota = $_POST['cantidad_mascota'];

$estado = "Activo";

/* =========================
   VERIFICAR CORREO y NÚMERO DE DOCUMENTO
========================= */

$verificar_correo = "SELECT * FROM PERSONAS WHERE correo='$correo'";
$resultado_correo = mysqli_query($conexion,$verificar_correo);

if(mysqli_num_rows($resultado_correo) > 0){
    echo "<script>
    alert('Este correo ya está registrado');
    window.history.back();
    </script>";
    exit();
}

$verificar_documento = "SELECT * FROM PERSONAS WHERE numero_documento='$num_doc'";
$resultado_documento = mysqli_query($conexion,$verificar_documento);

if(mysqli_num_rows($resultado_documento) > 0){
    echo "<script>
    alert('Este número de documento ya está registrado');
    window.history.back();
    </script>";
    exit();
}

/* =========================
   INSERTAR PERSONA
========================= */

$sql_persona = "INSERT INTO PERSONAS
(nombre_completo,tipo_documento,numero_documento,telefono,correo,edad)
VALUES
('$nombre','$documento','$num_doc','$num_tlf','$correo','$edad')";

mysqli_query($conexion,$sql_persona);
$id_persona = mysqli_insert_id($conexion);

/* =========================
   CONTADORES INICIALES
========================= */

$total_personas = 1;
$total_adultos = 0;
$total_menores = 0;

if($edad >= 18){
    $total_adultos = 1;
} else {
    $total_menores = 1;
}

/* =========================
   USUARIO
========================= */

$sql_usuario = "INSERT INTO USUARIOS
(id_persona,contraseña,id_rol,estado)
VALUES
('$id_persona','$contrasena','$rol','$estado')";

mysqli_query($conexion,$sql_usuario);

/* =========================
   RESIDENTE
========================= */

$sql_residente = "INSERT INTO RESIDENTES (id_persona,profesion)
VALUES ('$id_persona','$profesion')";

mysqli_query($conexion,$sql_residente);
$id_residente = mysqli_insert_id($conexion);

/* =========================
   PROPIETARIO
========================= */

$sql_propietario = "INSERT INTO PROPIETARIOS (id_persona,direccion_residencia)
VALUES ('$id_persona','SIN DIRECCION')";

mysqli_query($conexion,$sql_propietario);
$id_propietario = mysqli_insert_id($conexion);

/* =========================
   BUSCAR TORRE
========================= */

$sql_buscar_torre = "SELECT id_torre FROM TORRES WHERE nombre='$num_torre'";
$resultado_torre = mysqli_query($conexion,$sql_buscar_torre);

if(mysqli_num_rows($resultado_torre) > 0){
    $fila = mysqli_fetch_assoc($resultado_torre);
    $id_torre = $fila['id_torre'];
} else {
    die("Error: la torre no existe");
}

/* =========================
   INMUEBLE
========================= */

$sql_inmueble = "INSERT INTO INMUEBLES
(numero,id_torre,area,parqueadero,id_propietario,total_personas,total_adultos,total_menores)
VALUES
('$num_inmueble','$id_torre','$area','$parqueadero','$id_propietario',0,0,0)";

mysqli_query($conexion,$sql_inmueble);
$id_inmueble = mysqli_insert_id($conexion);

/* =========================
   RELACION RESIDENTE - INMUEBLE
========================= */

$sql_relacion = "INSERT INTO RESIDENTE_INMUEBLE
(id_residente,id_inmueble,fecha_ingreso)
VALUES
('$id_residente','$id_inmueble',CURDATE())";

mysqli_query($conexion,$sql_relacion);

/* =========================
   PERSONAS DEL INMUEBLE
========================= */

// Los contadores ya tienen el registro del responsable definido arriba (1 persona)
if(isset($_POST['personas'])){

    foreach($_POST['personas'] as $persona){

        $nombre_p = $persona['nombre'];
        $edad_p = $persona['edad'];

        // Clasificación automática
        if($edad_p >= 18){
            $total_adultos++;
        } else {
            $total_menores++;
        }

        $total_personas++;

        /* PERSONA */
        $sql_persona_extra = "INSERT INTO PERSONAS (nombre_completo, edad)
        VALUES ('$nombre_p', '$edad_p')";
        mysqli_query($conexion,$sql_persona_extra);

        $id_persona_extra = mysqli_insert_id($conexion);

        /* RESIDENTE */
        $sql_residente_extra = "INSERT INTO RESIDENTES (id_persona)
        VALUES ('$id_persona_extra')";
        mysqli_query($conexion,$sql_residente_extra);

        $id_residente_extra = mysqli_insert_id($conexion);

        /* RELACION */
        $sql_relacion_extra = "INSERT INTO RESIDENTE_INMUEBLE
        (id_residente,id_inmueble,fecha_ingreso)
        VALUES
        ('$id_residente_extra','$id_inmueble',CURDATE())";

        mysqli_query($conexion,$sql_relacion_extra);
    }
}

/* =========================
   ACTUALIZAR TOTALES
========================= */

$sql_update = "UPDATE INMUEBLES SET
total_personas = '$total_personas',
total_adultos = '$total_adultos',
total_menores = '$total_menores'
WHERE id_inmueble = '$id_inmueble'";

mysqli_query($conexion,$sql_update);

/* =========================
   CONTACTO EMERGENCIA
========================= */

$sql_emergencia = "INSERT INTO CONTACTOS_DE_EMERGENCIA
(id_residente,nombre,telefono,relacion)
VALUES
('$id_residente','$nombre_emergencia','$num_tlf_emergencia','$relacion')";

mysqli_query($conexion,$sql_emergencia);

/* =========================
   MASCOTAS
========================= */

$sql_mascota = "INSERT INTO MASCOTAS
(id_residente,tipo,raza,cantidad)
VALUES
('$id_residente','$tipo_mascota','$raza','$cantidad_mascota')";

mysqli_query($conexion,$sql_mascota);

/* =========================
   FINAL
========================= */

echo "<script>
alert('Usuario registrado correctamente');
window.location.href='../html/propietario_crear.html';
</script>";

exit();

?>