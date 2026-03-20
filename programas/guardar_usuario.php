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

$es_actualizacion = false;

/* VERIFICAR SI LA PERSONA YA EXISTE POR NÚMERO DE DOCUMENTO */

$verificar_documento = "SELECT id_persona FROM PERSONAS WHERE numero_documento='$num_doc'";
$resultado_documento = mysqli_query($conexion,$verificar_documento);

if(mysqli_num_rows($resultado_documento) > 0){
    // LA PERSONA YA EXISTE - ACTUALIZAR
    $es_actualizacion = true;
    $fila = mysqli_fetch_assoc($resultado_documento);
    $id_persona = $fila['id_persona'];
    
    $sql_persona = "UPDATE PERSONAS SET
    nombre_completo='$nombre',
    tipo_documento='$documento',
    telefono='$num_tlf',
    correo='$correo'
    WHERE id_persona='$id_persona'";
} else {
    // LA PERSONA NO EXISTE - INSERTAR
    $verificar_correo = "SELECT * FROM PERSONAS WHERE correo='$correo'";
    $resultado_correo = mysqli_query($conexion,$verificar_correo);
    
    if(mysqli_num_rows($resultado_correo) > 0){
        echo "<script>
        alert('Este correo ya está registrado para otra persona');
        window.history.back();
        </script>";
        exit();
    }
    
    $sql_persona = "INSERT INTO PERSONAS
    (nombre_completo,tipo_documento,numero_documento,telefono,correo)
    VALUES
    ('$nombre','$documento','$num_doc','$num_tlf','$correo')";
}

$resultado_persona = mysqli_query($conexion,$sql_persona);

if(!$resultado_persona){
    die("Error al guardar persona");
}

if(mysqli_num_rows($resultado_documento) == 0){
    $id_persona = mysqli_insert_id($conexion);
}

/*INSERTAR O ACTUALIZAR USUARIO */

$verificar_usuario = "SELECT id_usuario FROM USUARIOS WHERE id_persona='$id_persona'";
$resultado_usuario_existe = mysqli_query($conexion,$verificar_usuario);

if(mysqli_num_rows($resultado_usuario_existe) > 0){
    $fila_usuario = mysqli_fetch_assoc($resultado_usuario_existe);
    $id_usuario = $fila_usuario['id_usuario'];
    
    $sql_usuario = "UPDATE USUARIOS SET
    contraseña='$contrasena',
    id_rol='$rol',
    estado='$estado'
    WHERE id_usuario='$id_usuario'";
} else {
    $sql_usuario = "INSERT INTO USUARIOS
    (id_persona,contraseña,id_rol,estado)
    VALUES
    ('$id_persona','$contrasena','$rol','$estado')";
}

$resultado_usuario = mysqli_query($conexion,$sql_usuario);

if(!$resultado_usuario){
    die("Error al guardar usuario");
}

/*INSERTAR O ACTUALIZAR RESIDENTE */

$verificar_residente = "SELECT id_residente FROM RESIDENTES WHERE id_persona='$id_persona'";
$resultado_residente_existe = mysqli_query($conexion,$verificar_residente);

if(mysqli_num_rows($resultado_residente_existe) > 0){
    $fila_residente = mysqli_fetch_assoc($resultado_residente_existe);
    $id_residente = $fila_residente['id_residente'];
    
    $sql_residente = "UPDATE RESIDENTES SET
    profesion='$profesion'
    WHERE id_residente='$id_residente'";
} else {
    $sql_residente = "INSERT INTO RESIDENTES (id_persona,profesion)
    VALUES ('$id_persona','$profesion')";
}

$resultado_residente = mysqli_query($conexion,$sql_residente);

if(!$resultado_residente){
    die("Error al guardar residente");
}

if(mysqli_num_rows($resultado_residente_existe) == 0){
    $id_residente = mysqli_insert_id($conexion);
}

/*INSERTAR O ACTUALIZAR PROPIETARIO */

$verificar_propietario = "SELECT id_propietario FROM PROPIETARIOS WHERE id_persona='$id_persona'";
$resultado_propietario_existe = mysqli_query($conexion,$verificar_propietario);

if(mysqli_num_rows($resultado_propietario_existe) > 0){
    $fila_propietario = mysqli_fetch_assoc($resultado_propietario_existe);
    $id_propietario = $fila_propietario['id_propietario'];
} else {
    $sql_propietario = "INSERT INTO PROPIETARIOS (id_persona,direccion_residencia)
    VALUES ('$id_persona','SIN DIRECCION')";
    
    $resultado_propietario = mysqli_query($conexion,$sql_propietario);
    
    if(!$resultado_propietario){
        die("Error al crear propietario");
    }
    
    $id_propietario = mysqli_insert_id($conexion);
}

/* BUSCAR TORRE */

$sql_buscar_torre = "SELECT id_torre FROM TORRES WHERE nombre='$num_torre'";
$resultado_torre = mysqli_query($conexion,$sql_buscar_torre);

if(mysqli_num_rows($resultado_torre) > 0){
    $fila = mysqli_fetch_assoc($resultado_torre);
    $id_torre = $fila['id_torre'];
} else {
    die("Error: la torre no existe");
}

/* INSERTAR O ACTUALIZAR INMUEBLE */

$verificar_inmueble = "SELECT id_inmueble FROM INMUEBLES 
WHERE numero='$num_inmueble'";
$resultado_inmueble_existe = mysqli_query($conexion,$verificar_inmueble);

if(mysqli_num_rows($resultado_inmueble_existe) > 0){
    $fila_inmueble = mysqli_fetch_assoc($resultado_inmueble_existe);
    $id_inmueble = $fila_inmueble['id_inmueble'];
    
    $sql_inmueble = "UPDATE INMUEBLES SET
    id_torre='$id_torre',
    area='$area',
    parqueadero='$parqueadero',
    id_propietario='$id_propietario'
    WHERE id_inmueble='$id_inmueble'";
} else {
    $sql_inmueble = "INSERT INTO INMUEBLES
    (numero,id_torre,area,parqueadero,id_propietario)
    VALUES
    ('$num_inmueble','$id_torre','$area','$parqueadero','$id_propietario')";
}

$resultado_inmueble = mysqli_query($conexion,$sql_inmueble);

if(!$resultado_inmueble){
    die("Error al guardar inmueble");
}

if(mysqli_num_rows($resultado_inmueble_existe) == 0){
    $id_inmueble = mysqli_insert_id($conexion);
}

/* RELACION RESIDENTE - INMUEBLE */

$verificar_relacion = "SELECT * FROM RESIDENTE_INMUEBLE 
WHERE id_residente='$id_residente' AND id_inmueble='$id_inmueble'";
$resultado_relacion_existe = mysqli_query($conexion,$verificar_relacion);

if(mysqli_num_rows($resultado_relacion_existe) == 0){
    $sql_relacion = "INSERT INTO RESIDENTE_INMUEBLE
    (id_residente,id_inmueble,fecha_ingreso)
    VALUES
    ('$id_residente','$id_inmueble',CURDATE())";
    
    mysqli_query($conexion,$sql_relacion);
}

/* CONTACTO DE EMERGENCIA */

$verificar_emergencia = "SELECT id_contacto FROM CONTACTOS_DE_EMERGENCIA 
WHERE id_residente='$id_residente'";
$resultado_emergencia_existe = mysqli_query($conexion,$verificar_emergencia);

if(mysqli_num_rows($resultado_emergencia_existe) > 0){
    $fila_emergencia = mysqli_fetch_assoc($resultado_emergencia_existe);
    $id_contacto = $fila_emergencia['id_contacto'];
    
    $sql_emergencia = "UPDATE CONTACTOS_DE_EMERGENCIA SET
    nombre='$nombre_emergencia',
    telefono='$num_tlf_emergencia',
    relacion='$relacion'
    WHERE id_contacto='$id_contacto'";
} else {
    $sql_emergencia = "INSERT INTO CONTACTOS_DE_EMERGENCIA
    (id_residente,nombre,telefono,relacion)
    VALUES
    ('$id_residente','$nombre_emergencia','$num_tlf_emergencia','$relacion')";
}

mysqli_query($conexion,$sql_emergencia);

/* MASCOTAS */

$verificar_mascota = "SELECT id_mascota FROM MASCOTAS 
WHERE id_residente='$id_residente'";
$resultado_mascota_existe = mysqli_query($conexion,$verificar_mascota);

if(mysqli_num_rows($resultado_mascota_existe) > 0){
    $fila_mascota = mysqli_fetch_assoc($resultado_mascota_existe);
    $id_mascota = $fila_mascota['id_mascota'];
    
    $sql_mascota = "UPDATE MASCOTAS SET
    tipo='$tipo_mascota',
    raza='$raza',
    cantidad='$cantidad_mascota'
    WHERE id_mascota='$id_mascota'";
} else {
    $sql_mascota = "INSERT INTO MASCOTAS
    (id_residente,tipo,raza,cantidad)
    VALUES
    ('$id_residente','$tipo_mascota','$raza','$cantidad_mascota')";
}

mysqli_query($conexion,$sql_mascota);

/* FINAL */

if($es_actualizacion){
    echo "<script>
    alert('Usuario actualizado correctamente');
    window.location.href='../html/crear.html';
    </script>";
} else {
    echo "<script>
    alert('Usuario registrado correctamente');
    window.location.href='../html/crear.html';
    </script>";
}

exit();

?>