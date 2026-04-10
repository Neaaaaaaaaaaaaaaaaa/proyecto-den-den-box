<?php
session_start();
include("../comun/conexion.php");

mysqli_query($conexion, "CREATE TABLE IF NOT EXISTS AJUSTES_SALDO_PENDIENTE (
	id_ajuste INT AUTO_INCREMENT PRIMARY KEY,
	id_inmueble INT NOT NULL,
	saldo_anterior DECIMAL(12,2) NOT NULL,
	nuevo_saldo DECIMAL(12,2) NOT NULL,
	motivo VARCHAR(255) DEFAULT 'Ajuste manual por administrador',
	id_usuario_admin INT NULL,
	fecha_ajuste DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (id_inmueble) REFERENCES INMUEBLES(id_inmueble),
	FOREIGN KEY (id_usuario_admin) REFERENCES USUARIOS(id_usuario)
)");

$id_inmueble_sql = isset($_SESSION['id_inmueble']) ? intval($_SESSION['id_inmueble']) : 'NULL';

if(isset($_POST['nombre'], $_POST['descripcion'], $_POST['fecha_pago'], $_POST['valor'], $_POST['metodo_pago'], $_POST['estado_pago'], $_FILES['archivo'])){

		$nombre = $_POST['nombre'];
		$descripcion = $_POST['descripcion'];
		$fecha_pago = $_POST['fecha_pago'];
		$valor = $_POST['valor'];
		$metodo_pago = $_POST['metodo_pago'];
		$estado_pago = trim($_POST['estado_pago']);
		if(strtolower($estado_pago) === 'al dia'){
			$estado_pago = 'Pagado';
		}

		$archivo = basename($_FILES['archivo']['name']);
		$directorio_uploads = __DIR__ . '/../../uploads/';
		if (!is_dir($directorio_uploads)) {
			mkdir($directorio_uploads, 0755, true);
		}
		$ruta = $directorio_uploads . $archivo;

		if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta)) {
			$respuesta = 'No se pudo guardar el archivo en el servidor.';
		}

		if (!isset($respuesta)) {
			$sql = "INSERT INTO pagos(id_inmueble, nombre, descripcion, archivo, fecha_pago, valor, metodo_pago, estado_pago)
						VALUES($id_inmueble_sql,'$nombre','$descripcion','$archivo','$fecha_pago','$valor','$metodo_pago','$estado_pago')";

		if(mysqli_query($conexion, $sql)){
			if($id_inmueble_sql !== 'NULL' && strcasecmp($estado_pago, 'Pagado') === 0){
				$id_inmueble_num = intval($id_inmueble_sql);
				$sql_saldo_actual = "SELECT
					COALESCE((SELECT SUM(valor) FROM PAGOS WHERE id_inmueble = $id_inmueble_num AND estado_pago = 'Pendiente'), 0)
					+ COALESCE((SELECT SUM(nuevo_saldo - saldo_anterior) FROM AJUSTES_SALDO_PENDIENTE WHERE id_inmueble = $id_inmueble_num), 0)
					AS saldo_actual";

				$r_saldo = mysqli_query($conexion, $sql_saldo_actual);
				if($r_saldo){
					$f_saldo = mysqli_fetch_assoc($r_saldo);
					$saldo_actual = isset($f_saldo['saldo_actual']) ? floatval($f_saldo['saldo_actual']) : 0;
					$valor_pago = floatval($valor);
					$nuevo_saldo = $saldo_actual - $valor_pago;
					if($nuevo_saldo < 0){
						$nuevo_saldo = 0;
					}

					$id_usuario = isset($_SESSION['id_usuario']) ? intval($_SESSION['id_usuario']) : 'NULL';
					$motivo_sql = mysqli_real_escape_string($conexion, 'Descuento automatico por pago registrado por residente');
					$sql_ajuste = "INSERT INTO AJUSTES_SALDO_PENDIENTE (id_inmueble, saldo_anterior, nuevo_saldo, motivo, id_usuario_admin)
									 VALUES ($id_inmueble_num, $saldo_actual, $nuevo_saldo, '$motivo_sql', $id_usuario)";
					mysqli_query($conexion, $sql_ajuste);
				}
			}
				$respuesta = "ok";
		} else {
				$respuesta = "Error en la base de datos: " . mysqli_error($conexion);
		}
		}

} else {
		$respuesta = "Faltan datos";
}
?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Registrar Pagos — Den Den Box</title>
	<link rel="shortcut icon" href="../../img/warzone.svg" type="image/x-icon">
	<link rel="stylesheet" href="../../css/style.css">
</head>
<body>
<header class="header">
	<div class="container navbar">
		<a class="brand" href="index.html">
			<img src="../../img/logo.png">
			<div>
				<div class="title">Den Den Box</div>
				<div class="subtitle">Registrar pago</div>
			</div>
		</a>
		<nav class="nav-links">
			<a href="../../html/residente/index_residente.html">Inicio</a>
			<a href="dashboard_residente.php">Dashboard residente</a>
			<a href="../../programas/auth/logout.php" class="btn-login">Cerrar Sesión</a>
		</nav>
	</div>
</header>

<main class="container" style="padding-top:28px;">
	<div style="margin-top:20px;max-width:600px;background:white;padding:20px;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,0.06);text-align:center;">
		<?php if($respuesta === "ok"): ?>
			<h2 style="color:green;margin-bottom:20px;">✓ Pago subido correctamente</h2>
			<p style="margin-bottom:20px;color:#666;">Tu pago ha sido registrado en el sistema.</p>
			<a href="dashboard_residente.php" style="display:inline-block;background:var(--primary);color:white;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:700;">Volver al Dashboard</a>
		<?php else: ?>
			<h2 style="color:red;margin-bottom:20px;">✗ Error al registrar el pago</h2>
			<p style="margin-bottom:20px;color:#666;"><?php echo $respuesta; ?></p>
			<a href="../../html/residente/placeholders/registrar_pagos.html" style="display:inline-block;background:var(--primary);color:white;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:700;">Volver al formulario</a>
		<?php endif; ?>
	</div>
	<section class="footer">© 2025 Den Den Box — Proyecto SENA</section>
</main>

</body>
</html>
