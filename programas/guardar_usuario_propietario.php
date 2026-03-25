<?php
session_start();

include("conexion.php");

if(!$conexion){
    die("Error: no hay conexión con la base de datos");
}

if(!isset($_SESSION['rol']) || intval($_SESSION['rol']) !== 4 || !isset($_SESSION['id_propietario'])){
    echo "<script>alert('Debes iniciar sesión como propietario para registrar residentes.');window.location.href='../html/login.html';</script>";
    exit();
}

$id_propietario_sesion = intval($_SESSION['id_propietario']);

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

$id_inmueble_post = isset($_POST['id_inmueble']) ? intval($_POST['id_inmueble']) : 0;
$area = $_POST['area'];
$parqueadero = $_POST['parqueadero'];

$nombre_emergencia = $_POST['nombre_emergencia'];
$num_tlf_emergencia = $_POST['num_tlf_emergencia'];
$relacion = $_POST['relacion'];

$tipo_mascota = $_POST['tipo_mascota'];
$raza = $_POST['raza'];
$cantidad_mascota = $_POST['cantidad_mascota'];
$adultos = isset($_POST['adultos']) && is_array($_POST['adultos']) ? $_POST['adultos'] : [];
$menores = isset($_POST['menores']) && is_array($_POST['menores']) ? $_POST['menores'] : [];

$estado = "Activo";

$es_actualizacion = false;

/* =========================
   VERIFICAR SI LA PERSONA YA EXISTE POR NÚMERO DE DOCUMENTO
========================= */

$verificar_documento = "SELECT * FROM PERSONAS WHERE numero_documento='$num_doc'";
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
    correo='$correo',
    edad='$edad'
    WHERE id_persona='$id_persona'";
} else {
    // LA PERSONA NO EXISTE - VERIFICAR CORREO PARA OTRA PERSONA
    $verificar_correo = "SELECT * FROM PERSONAS WHERE correo='$correo'";
    $resultado_correo = mysqli_query($conexion,$verificar_correo);
    
    if(mysqli_num_rows($resultado_correo) > 0){
        echo "<script>
        alert('Este correo ya está registrado para otra persona');
        window.history.back();
        </script>";
        exit();
    }
    
    // INSERTAR NUEVA PERSONA
    $sql_persona = "INSERT INTO PERSONAS
    (nombre_completo,tipo_documento,numero_documento,telefono,correo,edad)
    VALUES
    ('$nombre','$documento','$num_doc','$num_tlf','$correo','$edad')";
}

mysqli_query($conexion,$sql_persona);

if(!$es_actualizacion){
    $id_persona = mysqli_insert_id($conexion);
}

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

mysqli_query($conexion,$sql_usuario);

/* =========================
   RESIDENTE
========================= */

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

mysqli_query($conexion,$sql_residente);

if(mysqli_num_rows($resultado_residente_existe) == 0){
    $id_residente = mysqli_insert_id($conexion);
}

/* =========================
     INMUEBLE
========================= */

if($id_inmueble_post <= 0){
        echo "<script>
        alert('Debes seleccionar un inmueble de la lista.');
        window.history.back();
        </script>";
        exit();
}

$verificar_inmueble = "SELECT i.id_inmueble
FROM INMUEBLES i
WHERE i.id_inmueble='$id_inmueble_post'
    AND i.id_propietario='$id_propietario_sesion'
LIMIT 1";
$resultado_inmueble_existe = mysqli_query($conexion,$verificar_inmueble);

if(mysqli_num_rows($resultado_inmueble_existe) > 0){
    $fila_inmueble = mysqli_fetch_assoc($resultado_inmueble_existe);
    $id_inmueble = $fila_inmueble['id_inmueble'];
} else {
    echo "<script>
    alert('El inmueble seleccionado no existe o no está vinculado a tu perfil de propietario.');
    window.history.back();
    </script>";
    exit();
}

/* =========================
   RELACION RESIDENTE - INMUEBLE
========================= */

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

/* =========================
   PERSONAS DEL INMUEBLE
========================= */

// Los contadores ya tienen el registro del responsable definido arriba (1 persona)
if(isset($_POST['personas']) && is_array($_POST['personas']) && !empty($_POST['personas'])){

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
} else {
    foreach($adultos as $nombre_adulto){
        $nombre_adulto = trim($nombre_adulto);
        if($nombre_adulto === ''){ continue; }

        $total_adultos++;
        $total_personas++;

        $nombre_adulto_sql = mysqli_real_escape_string($conexion, $nombre_adulto);
        $sql_persona_extra = "INSERT INTO PERSONAS (nombre_completo, edad)
        VALUES ('$nombre_adulto_sql', 18)";
        mysqli_query($conexion,$sql_persona_extra);

        $id_persona_extra = mysqli_insert_id($conexion);

        $sql_residente_extra = "INSERT INTO RESIDENTES (id_persona)
        VALUES ('$id_persona_extra')";
        mysqli_query($conexion,$sql_residente_extra);

        $id_residente_extra = mysqli_insert_id($conexion);

        $sql_relacion_extra = "INSERT INTO RESIDENTE_INMUEBLE
        (id_residente,id_inmueble,fecha_ingreso)
        VALUES
        ('$id_residente_extra','$id_inmueble',CURDATE())";
        mysqli_query($conexion,$sql_relacion_extra);
    }

    foreach($menores as $nombre_menor){
        $nombre_menor = trim($nombre_menor);
        if($nombre_menor === ''){ continue; }

        $total_menores++;
        $total_personas++;

        $nombre_menor_sql = mysqli_real_escape_string($conexion, $nombre_menor);
        $sql_persona_extra = "INSERT INTO PERSONAS (nombre_completo, edad)
        VALUES ('$nombre_menor_sql', 10)";
        mysqli_query($conexion,$sql_persona_extra);

        $id_persona_extra = mysqli_insert_id($conexion);

        $sql_residente_extra = "INSERT INTO RESIDENTES (id_persona)
        VALUES ('$id_persona_extra')";
        mysqli_query($conexion,$sql_residente_extra);

        $id_residente_extra = mysqli_insert_id($conexion);

        $sql_relacion_extra = "INSERT INTO RESIDENTE_INMUEBLE
        (id_residente,id_inmueble,fecha_ingreso)
        VALUES
        ('$id_residente_extra','$id_inmueble',CURDATE())";
        mysqli_query($conexion,$sql_relacion_extra);
    }
}

/* =========================
    ACTUALIZAR TOTALES REALES DEL INMUEBLE
========================= */

$sql_totales = "SELECT
COUNT(DISTINCT ri.id_residente) AS total_personas,
SUM(CASE WHEN COALESCE(p.edad,0) >= 18 THEN 1 ELSE 0 END) AS total_adultos,
SUM(CASE WHEN COALESCE(p.edad,0) < 18 THEN 1 ELSE 0 END) AS total_menores
FROM RESIDENTE_INMUEBLE ri
LEFT JOIN RESIDENTES r ON ri.id_residente = r.id_residente
LEFT JOIN PERSONAS p ON r.id_persona = p.id_persona
WHERE ri.id_inmueble = '$id_inmueble'";

$resultado_totales = mysqli_query($conexion, $sql_totales);

if($resultado_totales && mysqli_num_rows($resultado_totales) > 0){
     $fila_totales = mysqli_fetch_assoc($resultado_totales);
     $total_personas = intval($fila_totales['total_personas']);
     $total_adultos = intval($fila_totales['total_adultos']);
     $total_menores = intval($fila_totales['total_menores']);

     $sql_update = "UPDATE INMUEBLES SET
     total_personas = '$total_personas',
     total_adultos = '$total_adultos',
     total_menores = '$total_menores'
     WHERE id_inmueble = '$id_inmueble'";

     mysqli_query($conexion,$sql_update);
}

/* =========================
   CONTACTO EMERGENCIA
========================= */

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

/* =========================
   MASCOTAS
========================= */

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

/* =========================
   FINAL
========================= */

if($es_actualizacion){
    echo "<script>
    alert('Usuario actualizado correctamente');
    window.location.href='../html/propietario_crear.html';
    </script>";
} else {
    echo "<script>
    alert('Usuario registrado correctamente');
    window.location.href='../html/propietario_crear.html';
    </script>";
}

exit();

?>