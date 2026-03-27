<?php
session_start();
include("../comun/conexion.php");

if(!isset($_SESSION['rol']) || intval($_SESSION['rol']) !== 1){
	header("Location: pagos_realtime.php?status=unauthorized");
	exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
	header("Location: pagos_realtime.php?status=invalid");
	exit;
}

$id_inmueble = isset($_POST['id_inmueble']) ? intval($_POST['id_inmueble']) : 0;
$monto_agregar = isset($_POST['monto_agregar']) ? floatval($_POST['monto_agregar']) : -1;

if($id_inmueble <= 0 || $monto_agregar < 0){
	header("Location: pagos_realtime.php?status=invalid");
	exit;
}

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

$sql_saldo_actual = "SELECT
	COALESCE((SELECT SUM(valor) FROM PAGOS WHERE id_inmueble = $id_inmueble AND estado_pago = 'Pendiente'), 0)
	+ COALESCE((SELECT SUM(nuevo_saldo - saldo_anterior) FROM AJUSTES_SALDO_PENDIENTE WHERE id_inmueble = $id_inmueble), 0)
	AS saldo_actual";

$resultado = mysqli_query($conexion, $sql_saldo_actual);
if(!$resultado){
	header("Location: pagos_realtime.php?status=error");
	exit;
}

$fila = mysqli_fetch_assoc($resultado);
$saldo_actual = isset($fila['saldo_actual']) ? floatval($fila['saldo_actual']) : 0;
$nuevo_saldo = $saldo_actual + $monto_agregar;

$id_usuario_admin = isset($_SESSION['id_usuario']) ? intval($_SESSION['id_usuario']) : 'NULL';
$motivo = "Suma manual al saldo pendiente";
$motivo_sql = mysqli_real_escape_string($conexion, $motivo);

$sql_insert = "INSERT INTO AJUSTES_SALDO_PENDIENTE (id_inmueble, saldo_anterior, nuevo_saldo, motivo, id_usuario_admin)
			   VALUES ($id_inmueble, $saldo_actual, $nuevo_saldo, '$motivo_sql', $id_usuario_admin)";

if(mysqli_query($conexion, $sql_insert)){
	header("Location: pagos_realtime.php?status=updated&id_inmueble=$id_inmueble");
	exit;
}

header("Location: pagos_realtime.php?status=error");
exit;
