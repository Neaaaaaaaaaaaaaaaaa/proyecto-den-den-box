<?php

include("conexion.php");

if(!$conexion){
    die("Error: no hay conexión con la base de datos");
}

/* CAPTURA DE DATOS */

$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$contrasena = $_POST['contrasena'];
$rol = $_POST['rol'];
$documento = $_POST['documento'];
$num_doc = $_POST['num_doc'];
$num_tlf = $_POST['num_tlf'];

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

/* VERIFICAR CORREO */

$verificar = "SELECT * FROM PERSONAS WHERE correo='$correo'";
$resultado = mysqli_query($conexion,$verificar);

if(mysqli_num_rows($resultado) > 0){
    echo "<script>
    alert('Este correo ya está registrado');
    window.history.back();
    </script>";
    exit();
}

/* INSERTAR PERSONA */

$sql_persona = "INSERT INTO PERSONAS
(nombre_completo,tipo_documento,numero_documento,telefono,correo)
VALUES
('$nombre','$documento','$num_doc','$num_tlf','$correo')";

$resultado_persona = mysqli_query($conexion,$sql_persona);

if(!$resultado_persona){
    die("Error al crear persona");
}

$id_persona = mysqli_insert_id($conexion);

/*INSERTAR USUARIO */

$sql_usuario = "INSERT INTO USUARIOS
(id_persona,contraseña,id_rol,estado)
VALUES
('$id_persona','$contrasena','$rol','$estado')";

$resultado_usuario = mysqli_query($conexion,$sql_usuario);

if(!$resultado_usuario){
    die("Error al crear usuario");
}

/*INSERTAR RESIDENTE  */

$sql_residente = "INSERT INTO RESIDENTES (id_persona,profesion)
VALUES ('$id_persona','$profesion')";

$resultado_residente = mysqli_query($conexion,$sql_residente);

if(!$resultado_residente){
    die("Error al crear residente");
}

$id_residente = mysqli_insert_id($conexion);

/*INSERTAR PROPIETARIO */

$sql_propietario = "INSERT INTO PROPIETARIOS (id_persona,direccion_residencia)
VALUES ('$id_persona','SIN DIRECCION')";

$resultado_propietario = mysqli_query($conexion,$sql_propietario);

if(!$resultado_propietario){
    die("Error al crear propietario");
}

$id_propietario = mysqli_insert_id($conexion);

/* BUSCAR TORRE */

$sql_buscar_torre = "SELECT id_torre FROM TORRES WHERE nombre='$num_torre'";
$resultado_torre = mysqli_query($conexion,$sql_buscar_torre);

if(mysqli_num_rows($resultado_torre) > 0){
    $fila = mysqli_fetch_assoc($resultado_torre);
    $id_torre = $fila['id_torre'];
} else {
    die("Error: la torre no existe");
}

/* INSERTAR INMUEBLE */

$sql_inmueble = "INSERT INTO INMUEBLES
(numero,id_torre,area,parqueadero,id_propietario)
VALUES
('$num_inmueble','$id_torre','$area','$parqueadero','$id_propietario')";

$resultado_inmueble = mysqli_query($conexion,$sql_inmueble);

if(!$resultado_inmueble){
    die("Error al crear inmueble");
}

$id_inmueble = mysqli_insert_id($conexion);

/* RELACION RESIDENTE - INMUEBLE */

$sql_relacion = "INSERT INTO RESIDENTE_INMUEBLE
(id_residente,id_inmueble,fecha_ingreso)
VALUES
('$id_residente','$id_inmueble',CURDATE())";

mysqli_query($conexion,$sql_relacion);

/* CONTACTO DE EMERGENCIA */

$sql_emergencia = "INSERT INTO CONTACTOS_DE_EMERGENCIA
(id_residente,nombre,telefono,relacion)
VALUES
('$id_residente','$nombre_emergencia','$num_tlf_emergencia','$relacion')";

mysqli_query($conexion,$sql_emergencia);

/* MASCOTAS */

$sql_mascota = "INSERT INTO MASCOTAS
(id_residente,tipo,raza,cantidad)
VALUES
('$id_residente','$tipo_mascota','$raza','$cantidad_mascota')";

mysqli_query($conexion,$sql_mascota);

/* FINAL */

echo "<script>
alert('Usuario registrado correctamente');
window.location.href='../html/crear.html';
</script>";

exit();

?>