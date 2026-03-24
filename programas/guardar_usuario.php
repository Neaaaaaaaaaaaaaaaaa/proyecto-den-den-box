<?php
include("conexion.php");

if(!$conexion){
    die("Error: no hay conexión con la base de datos");
}

function post_value($key, $default = "") {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

function limpiar_residente_por_persona($conexion, $id_persona) {
    $q = mysqli_query($conexion, "SELECT id_residente FROM RESIDENTES WHERE id_persona=".intval($id_persona));
    while($r = mysqli_fetch_assoc($q)){
        $id_residente = intval($r['id_residente']);
        mysqli_query($conexion, "DELETE FROM CONTACTOS_DE_EMERGENCIA WHERE id_residente=$id_residente");
        mysqli_query($conexion, "DELETE FROM MASCOTAS WHERE id_residente=$id_residente");
        mysqli_query($conexion, "DELETE FROM RESIDENTE_INMUEBLE WHERE id_residente=$id_residente");
        mysqli_query($conexion, "DELETE FROM RESIDENTES WHERE id_residente=$id_residente");
    }
}

function limpiar_propietario_por_persona($conexion, $id_persona) {
    $q = mysqli_query($conexion, "SELECT id_propietario FROM PROPIETARIOS WHERE id_persona=".intval($id_persona));
    while($r = mysqli_fetch_assoc($q)){
        $id_propietario = intval($r['id_propietario']);
        mysqli_query($conexion, "UPDATE INMUEBLES SET id_propietario=NULL WHERE id_propietario=$id_propietario");
        mysqli_query($conexion, "DELETE FROM PROPIETARIOS WHERE id_propietario=$id_propietario");
    }
}

$nombre = mysqli_real_escape_string($conexion, post_value('nombre'));
$correo = mysqli_real_escape_string($conexion, post_value('correo'));
$contrasena = mysqli_real_escape_string($conexion, post_value('contrasena'));
$rol = intval(post_value('rol', '0'));
$documento = mysqli_real_escape_string($conexion, post_value('documento'));
$num_doc = mysqli_real_escape_string($conexion, post_value('num_doc'));
$num_tlf = mysqli_real_escape_string($conexion, post_value('num_tlf'));
$profesion = mysqli_real_escape_string($conexion, post_value('profesion'));

$num_inmueble = mysqli_real_escape_string($conexion, post_value('num_inmueble'));
$num_torre = mysqli_real_escape_string($conexion, post_value('num_torre'));
$area = post_value('area');
$parqueadero = post_value('parqueadero');

$nombre_emergencia = mysqli_real_escape_string($conexion, post_value('nombre_emergencia'));
$num_tlf_emergencia = mysqli_real_escape_string($conexion, post_value('num_tlf_emergencia'));
$relacion = mysqli_real_escape_string($conexion, post_value('relacion'));

$tipo_mascota = mysqli_real_escape_string($conexion, post_value('tipo_mascota'));
$raza = mysqli_real_escape_string($conexion, post_value('raza'));
$cantidad_mascota = post_value('cantidad_mascota');
$adultos = isset($_POST['adultos']) && is_array($_POST['adultos']) ? $_POST['adultos'] : [];
$menores = isset($_POST['menores']) && is_array($_POST['menores']) ? $_POST['menores'] : [];

if($nombre === '' || $correo === '' || $contrasena === '' || $num_doc === '' || $num_tlf === '' || $rol <= 0){
    echo "<script>alert('Faltan campos obligatorios');window.history.back();</script>";
    exit();
}

mysqli_begin_transaction($conexion);

try {
    $sql_inmueble = "SELECT i.id_inmueble
                    FROM INMUEBLES i
                    INNER JOIN TORRES t ON i.id_torre = t.id_torre
                    WHERE i.numero = '$num_inmueble' AND t.nombre = '$num_torre'
                    LIMIT 1";
    $res_inmueble = mysqli_query($conexion, $sql_inmueble);

    if(!$res_inmueble || mysqli_num_rows($res_inmueble) === 0){
        throw new Exception("El inmueble indicado no existe. Debes seleccionar un inmueble ya creado.");
    }

    $id_inmueble = intval(mysqli_fetch_assoc($res_inmueble)['id_inmueble']);

    $res_doc = mysqli_query($conexion, "SELECT id_persona FROM PERSONAS WHERE numero_documento='$num_doc' LIMIT 1");
    $es_actualizacion = ($res_doc && mysqli_num_rows($res_doc) > 0);

    if($es_actualizacion){
        $id_persona = intval(mysqli_fetch_assoc($res_doc)['id_persona']);
        $res_correo_otro = mysqli_query($conexion, "SELECT id_persona FROM PERSONAS WHERE correo='$correo' AND id_persona <> $id_persona LIMIT 1");
        if($res_correo_otro && mysqli_num_rows($res_correo_otro) > 0){
            throw new Exception("Este correo ya pertenece a otra persona.");
        }

        $sql_persona = "UPDATE PERSONAS SET
                        nombre_completo='$nombre',
                        tipo_documento='$documento',
                        telefono='$num_tlf',
                        correo='$correo'
                        WHERE id_persona=$id_persona";
        if(!mysqli_query($conexion, $sql_persona)){
            throw new Exception("No se pudo actualizar la persona.");
        }
    } else {
        $res_correo = mysqli_query($conexion, "SELECT id_persona FROM PERSONAS WHERE correo='$correo' LIMIT 1");
        if($res_correo && mysqli_num_rows($res_correo) > 0){
            throw new Exception("Este correo ya está registrado para otra persona.");
        }

        $sql_persona = "INSERT INTO PERSONAS (nombre_completo,tipo_documento,numero_documento,telefono,correo)
                        VALUES ('$nombre','$documento','$num_doc','$num_tlf','$correo')";
        if(!mysqli_query($conexion, $sql_persona)){
            throw new Exception("No se pudo crear la persona.");
        }
        $id_persona = mysqli_insert_id($conexion);
    }

    $estado = 'Activo';
    $res_usuario = mysqli_query($conexion, "SELECT id_usuario FROM USUARIOS WHERE id_persona=$id_persona LIMIT 1");
    if($res_usuario && mysqli_num_rows($res_usuario) > 0){
        $id_usuario = intval(mysqli_fetch_assoc($res_usuario)['id_usuario']);
        $sql_usuario = "UPDATE USUARIOS SET contraseña='$contrasena', id_rol=$rol, estado='$estado' WHERE id_usuario=$id_usuario";
    } else {
        $sql_usuario = "INSERT INTO USUARIOS (id_persona,contraseña,id_rol,estado) VALUES ($id_persona,'$contrasena',$rol,'$estado')";
    }
    if(!mysqli_query($conexion, $sql_usuario)){
        throw new Exception("No se pudo guardar el usuario.");
    }

    if($rol === 3){
        limpiar_propietario_por_persona($conexion, $id_persona);

        $res_residente = mysqli_query($conexion, "SELECT id_residente FROM RESIDENTES WHERE id_persona=$id_persona LIMIT 1");
        if($res_residente && mysqli_num_rows($res_residente) > 0){
            $id_residente = intval(mysqli_fetch_assoc($res_residente)['id_residente']);
            mysqli_query($conexion, "UPDATE RESIDENTES SET profesion='$profesion' WHERE id_residente=$id_residente");
        } else {
            mysqli_query($conexion, "INSERT INTO RESIDENTES (id_persona,profesion) VALUES ($id_persona,'$profesion')");
            $id_residente = mysqli_insert_id($conexion);
        }

        mysqli_query($conexion, "DELETE FROM RESIDENTE_INMUEBLE WHERE id_residente=$id_residente");
        mysqli_query($conexion, "INSERT INTO RESIDENTE_INMUEBLE (id_residente,id_inmueble,fecha_ingreso) VALUES ($id_residente,$id_inmueble,CURDATE())");

        foreach($adultos as $nombre_adulto){
            $nombre_adulto = trim($nombre_adulto);
            if($nombre_adulto === ''){ continue; }

            $nombre_adulto_sql = mysqli_real_escape_string($conexion, $nombre_adulto);
            mysqli_query($conexion, "INSERT INTO PERSONAS (nombre_completo, edad) VALUES ('$nombre_adulto_sql', 18)");
            $id_persona_extra = mysqli_insert_id($conexion);

            mysqli_query($conexion, "INSERT INTO RESIDENTES (id_persona, profesion) VALUES ($id_persona_extra, NULL)");
            $id_residente_extra = mysqli_insert_id($conexion);

            mysqli_query($conexion, "INSERT INTO RESIDENTE_INMUEBLE (id_residente,id_inmueble,fecha_ingreso) VALUES ($id_residente_extra,$id_inmueble,CURDATE())");
        }

        foreach($menores as $nombre_menor){
            $nombre_menor = trim($nombre_menor);
            if($nombre_menor === ''){ continue; }

            $nombre_menor_sql = mysqli_real_escape_string($conexion, $nombre_menor);
            mysqli_query($conexion, "INSERT INTO PERSONAS (nombre_completo, edad) VALUES ('$nombre_menor_sql', 10)");
            $id_persona_extra = mysqli_insert_id($conexion);

            mysqli_query($conexion, "INSERT INTO RESIDENTES (id_persona, profesion) VALUES ($id_persona_extra, NULL)");
            $id_residente_extra = mysqli_insert_id($conexion);

            mysqli_query($conexion, "INSERT INTO RESIDENTE_INMUEBLE (id_residente,id_inmueble,fecha_ingreso) VALUES ($id_residente_extra,$id_inmueble,CURDATE())");
        }

        $sql_totales = "SELECT
                          COUNT(DISTINCT ri.id_residente) AS total_personas,
                          SUM(CASE WHEN COALESCE(p.edad,0) >= 18 THEN 1 ELSE 0 END) AS total_adultos,
                          SUM(CASE WHEN COALESCE(p.edad,0) < 18 THEN 1 ELSE 0 END) AS total_menores
                        FROM RESIDENTE_INMUEBLE ri
                        LEFT JOIN RESIDENTES r ON ri.id_residente = r.id_residente
                        LEFT JOIN PERSONAS p ON r.id_persona = p.id_persona
                        WHERE ri.id_inmueble=$id_inmueble";
        $res_totales = mysqli_query($conexion, $sql_totales);
        if($res_totales && mysqli_num_rows($res_totales) > 0){
            $fila_totales = mysqli_fetch_assoc($res_totales);
            $total_personas = intval($fila_totales['total_personas']);
            $total_adultos = intval($fila_totales['total_adultos']);
            $total_menores = intval($fila_totales['total_menores']);

            mysqli_query($conexion, "UPDATE INMUEBLES
                                     SET total_personas=$total_personas,
                                         total_adultos=$total_adultos,
                                         total_menores=$total_menores
                                     WHERE id_inmueble=$id_inmueble");
        }

        $res_emergencia = mysqli_query($conexion, "SELECT id_contacto FROM CONTACTOS_DE_EMERGENCIA WHERE id_residente=$id_residente LIMIT 1");
        if($res_emergencia && mysqli_num_rows($res_emergencia) > 0){
            $id_contacto = intval(mysqli_fetch_assoc($res_emergencia)['id_contacto']);
            mysqli_query($conexion, "UPDATE CONTACTOS_DE_EMERGENCIA SET nombre='$nombre_emergencia', telefono='$num_tlf_emergencia', relacion='$relacion' WHERE id_contacto=$id_contacto");
        } else {
            mysqli_query($conexion, "INSERT INTO CONTACTOS_DE_EMERGENCIA (id_residente,nombre,telefono,relacion) VALUES ($id_residente,'$nombre_emergencia','$num_tlf_emergencia','$relacion')");
        }

        if($tipo_mascota !== '' || $raza !== '' || $cantidad_mascota !== ''){
            $cantidad_mascota_val = ($cantidad_mascota === '') ? 'NULL' : intval($cantidad_mascota);
            $res_mascota = mysqli_query($conexion, "SELECT id_mascota FROM MASCOTAS WHERE id_residente=$id_residente LIMIT 1");
            if($res_mascota && mysqli_num_rows($res_mascota) > 0){
                $id_mascota = intval(mysqli_fetch_assoc($res_mascota)['id_mascota']);
                mysqli_query($conexion, "UPDATE MASCOTAS SET tipo='$tipo_mascota', raza='$raza', cantidad=$cantidad_mascota_val WHERE id_mascota=$id_mascota");
            } else {
                mysqli_query($conexion, "INSERT INTO MASCOTAS (id_residente,tipo,raza,cantidad) VALUES ($id_residente,'$tipo_mascota','$raza',$cantidad_mascota_val)");
            }
        }

    } elseif($rol === 4){
        limpiar_residente_por_persona($conexion, $id_persona);

        $res_prop = mysqli_query($conexion, "SELECT id_propietario FROM PROPIETARIOS WHERE id_persona=$id_persona LIMIT 1");
        if($res_prop && mysqli_num_rows($res_prop) > 0){
            $id_propietario = intval(mysqli_fetch_assoc($res_prop)['id_propietario']);
        } else {
            mysqli_query($conexion, "INSERT INTO PROPIETARIOS (id_persona,direccion_residencia) VALUES ($id_persona,'SIN DIRECCION')");
            $id_propietario = mysqli_insert_id($conexion);
        }

        mysqli_query($conexion, "UPDATE INMUEBLES SET id_propietario=$id_propietario WHERE id_inmueble=$id_inmueble");

    } else {
        limpiar_residente_por_persona($conexion, $id_persona);
        limpiar_propietario_por_persona($conexion, $id_persona);
    }

    if($area !== '' || $parqueadero !== ''){
        $set_area = ($area === '') ? "area=area" : "area=".floatval($area);
        $set_parq = ($parqueadero === '') ? "parqueadero=parqueadero" : "parqueadero='".mysqli_real_escape_string($conexion,$parqueadero)."'";
        mysqli_query($conexion, "UPDATE INMUEBLES SET $set_area, $set_parq WHERE id_inmueble=$id_inmueble");
    }

    mysqli_commit($conexion);

    if($es_actualizacion){
        echo "<script>alert('Usuario actualizado correctamente y asignado al inmueble existente');window.location.href='../html/crear.html';</script>";
    } else {
        echo "<script>alert('Usuario registrado correctamente y asignado al inmueble existente');window.location.href='../html/crear.html';</script>";
    }

} catch (Exception $e) {
    mysqli_rollback($conexion);
    $msg = addslashes($e->getMessage());
    echo "<script>alert('$msg');window.history.back();</script>";
}

exit();
?>