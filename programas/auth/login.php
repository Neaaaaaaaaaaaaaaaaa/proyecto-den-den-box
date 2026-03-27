<?php

session_start();
include("../comun/conexion.php");

$usuario = $_POST['usuario'];
$contrasena = $_POST['contrasena'];

$sql = "SELECT * 
		FROM usuarios u
		INNER JOIN personas p ON u.id_persona = p.id_persona
		WHERE p.correo = '$usuario' 
		AND u.contraseña = '$contrasena'";

$resultado = mysqli_query($conexion,$sql);

if(mysqli_num_rows($resultado) > 0){

	$datos = mysqli_fetch_assoc($resultado);

	$estado_usuario = isset($datos['estado']) ? trim((string)$datos['estado']) : '';
	$usuario_activo = ($estado_usuario === '1' || strcasecmp($estado_usuario, 'Activo') === 0);

	if(!$usuario_activo){
		session_unset();
		session_destroy();
		header("Location: ../../html/comun/index.html");
		exit();
	}

	$_SESSION['correo'] = $datos['correo'];
	$_SESSION['rol'] = $datos['id_rol'];
	$_SESSION['id_persona'] = $datos['id_persona'];
	$_SESSION['id_usuario'] = $datos['id_usuario'];

	if($datos['id_rol'] == 3){
		$sql_residente = "SELECT id_residente FROM RESIDENTES WHERE id_persona = {$datos['id_persona']}";
		$r_res = mysqli_query($conexion, $sql_residente);
		if(mysqli_num_rows($r_res) > 0){
			$fila_res = mysqli_fetch_assoc($r_res);
			$_SESSION['id_residente'] = $fila_res['id_residente'];

			$sql_inmueble = "SELECT i.id_inmueble, i.numero AS apto, t.nombre AS torre
							 FROM RESIDENTE_INMUEBLE ri
							 JOIN INMUEBLES i ON ri.id_inmueble = i.id_inmueble
							 JOIN TORRES t ON i.id_torre = t.id_torre
							 WHERE ri.id_residente = {$fila_res['id_residente']}";
			$r_inm = mysqli_query($conexion, $sql_inmueble);
			if(mysqli_num_rows($r_inm) > 0){
				$fila_inm = mysqli_fetch_assoc($r_inm);
				$_SESSION['id_inmueble'] = $fila_inm['id_inmueble'];
				$_SESSION['apto'] = $fila_inm['apto'];
				$_SESSION['torre'] = $fila_inm['torre'];
			}
		}
	}

	if($datos['id_rol'] == 4){
		$sql_propietario = "SELECT id_propietario FROM PROPIETARIOS WHERE id_persona = {$datos['id_persona']}";
		$r_prop = mysqli_query($conexion, $sql_propietario);
		if(mysqli_num_rows($r_prop) > 0){
			$fila_prop = mysqli_fetch_assoc($r_prop);
			$_SESSION['id_propietario'] = $fila_prop['id_propietario'];
		}
	}

	if($datos['id_rol'] == 1){
		header("Location: ../../html/admin/index_admin.html");
		exit();
	} elseif($datos['id_rol'] == 2){
		header("Location: ../../html/operador/index_operador.html");
		exit();
	} elseif($datos['id_rol'] == 3){
		header("Location: ../../html/residente/index_residente.html");
		exit();
	} elseif($datos['id_rol'] == 4){
		header("Location: ../../html/propietario/index_propietario.html");
		exit();
	}

} else {
	echo "Usuario o contraseña incorrectos";
}
