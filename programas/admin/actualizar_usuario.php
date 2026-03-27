<?php
include("../comun/conexion.php");

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

$id_usuario = intval(post_value('id_usuario', '0'));
if($id_usuario <= 0){
	die("Usuario inválido");
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
$nombre_emergencia = mysqli_real_escape_string($conexion, post_value('nombre_emergencia'));
$num_tlf_emergencia = mysqli_real_escape_string($conexion, post_value('num_tlf_emergencia'));
$relacion = mysqli_real_escape_string($conexion, post_value('relacion'));
$tipo_mascota = mysqli_real_escape_string($conexion, post_value('tipo_mascota'));
$raza = mysqli_real_escape_string($conexion, post_value('raza'));
$cantidad_mascota = post_value('cantidad_mascota');

mysqli_begin_transaction($conexion);

try {
	$res_u = mysqli_query($conexion, "SELECT id_persona FROM USUARIOS WHERE id_usuario=$id_usuario LIMIT 1");
	if(!$res_u || mysqli_num_rows($res_u) === 0){
		throw new Exception("No se encontró el usuario");
	}
	$id_persona = intval(mysqli_fetch_assoc($res_u)['id_persona']);

	$res_doc_otro = mysqli_query($conexion, "SELECT id_persona FROM PERSONAS WHERE numero_documento='$num_doc' AND id_persona <> $id_persona LIMIT 1");
	if($res_doc_otro && mysqli_num_rows($res_doc_otro) > 0){
		throw new Exception("El número de documento ya existe para otra persona");
	}

	$res_correo_otro = mysqli_query($conexion, "SELECT id_persona FROM PERSONAS WHERE correo='$correo' AND id_persona <> $id_persona LIMIT 1");
	if($res_correo_otro && mysqli_num_rows($res_correo_otro) > 0){
		throw new Exception("El correo ya existe para otra persona");
	}

	$res_inmueble = mysqli_query($conexion, "SELECT i.id_inmueble
										  FROM INMUEBLES i
										  INNER JOIN TORRES t ON t.id_torre = i.id_torre
										  WHERE i.numero='$num_inmueble' AND t.nombre='$num_torre'
										  LIMIT 1");
	if(!$res_inmueble || mysqli_num_rows($res_inmueble) === 0){
		throw new Exception("El inmueble indicado no existe");
	}
	$id_inmueble = intval(mysqli_fetch_assoc($res_inmueble)['id_inmueble']);

	mysqli_query($conexion, "UPDATE PERSONAS SET
							nombre_completo='$nombre',
							tipo_documento='$documento',
							numero_documento='$num_doc',
							telefono='$num_tlf',
							correo='$correo'
							WHERE id_persona=$id_persona");

	mysqli_query($conexion, "UPDATE USUARIOS SET contraseña='$contrasena', id_rol=$rol, estado='Activo' WHERE id_usuario=$id_usuario");

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

		if($nombre_emergencia !== '' || $num_tlf_emergencia !== '' || $relacion !== ''){
			$res_em = mysqli_query($conexion, "SELECT id_contacto FROM CONTACTOS_DE_EMERGENCIA WHERE id_residente=$id_residente LIMIT 1");
			if($res_em && mysqli_num_rows($res_em) > 0){
				$id_contacto = intval(mysqli_fetch_assoc($res_em)['id_contacto']);
				mysqli_query($conexion, "UPDATE CONTACTOS_DE_EMERGENCIA SET nombre='$nombre_emergencia', telefono='$num_tlf_emergencia', relacion='$relacion' WHERE id_contacto=$id_contacto");
			} else {
				mysqli_query($conexion, "INSERT INTO CONTACTOS_DE_EMERGENCIA (id_residente,nombre,telefono,relacion) VALUES ($id_residente,'$nombre_emergencia','$num_tlf_emergencia','$relacion')");
			}
		}

		if($tipo_mascota !== '' || $raza !== '' || $cantidad_mascota !== ''){
			$cantidad_mascota_val = ($cantidad_mascota === '') ? 'NULL' : intval($cantidad_mascota);
			$res_m = mysqli_query($conexion, "SELECT id_mascota FROM MASCOTAS WHERE id_residente=$id_residente LIMIT 1");
			if($res_m && mysqli_num_rows($res_m) > 0){
				$id_mascota = intval(mysqli_fetch_assoc($res_m)['id_mascota']);
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

	mysqli_commit($conexion);
	echo "<script>alert('Usuario modificado correctamente');window.location.href='../../html/admin/crear.html';</script>";

} catch (Exception $e) {
	mysqli_rollback($conexion);
	$msg = addslashes($e->getMessage());
	echo "<script>alert('$msg');window.history.back();</script>";
}

exit;
