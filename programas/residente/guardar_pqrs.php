<?php
session_start();
include '../comun/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: ../../html/residente/placeholders/registrar_pqrs.html');
	exit();
}

$rol = intval($_SESSION['rol'] ?? 0);
if ($rol !== 3) {
	echo "<script>alert('No autorizado.'); window.location.href='../../html/comun/login.html';</script>";
	exit();
}

$tipo = trim($_POST['tipo'] ?? '');
$asunto = trim($_POST['asunto'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

if ($tipo === '' || $asunto === '' || $descripcion === '') {
	echo "<script>alert('Completa todos los campos del formulario.'); window.history.back();</script>";
	exit();
}

$residente_id = intval($_SESSION['id_residente'] ?? 0);

if ($residente_id <= 0 && isset($_SESSION['id_persona'])) {
	$id_persona = intval($_SESSION['id_persona']);
	$q_res = mysqli_query($conexion, "SELECT id_residente FROM RESIDENTES WHERE id_persona=$id_persona LIMIT 1");
	if ($q_res && mysqli_num_rows($q_res) > 0) {
		$residente_id = intval(mysqli_fetch_assoc($q_res)['id_residente']);
		$_SESSION['id_residente'] = $residente_id;
	}
}

if ($residente_id <= 0) {
	echo "<script>alert('No se encontro el residente asociado a tu usuario.'); window.history.back();</script>";
	exit();
}

$sql_tabla_pqrs = "CREATE TABLE IF NOT EXISTS PQRS (
	id_pqrs INT AUTO_INCREMENT PRIMARY KEY,
	id_residente INT NOT NULL,
	tipo VARCHAR(30) NOT NULL,
	asunto VARCHAR(150) NOT NULL,
	descripcion TEXT NOT NULL,
	fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	estado VARCHAR(30) NOT NULL DEFAULT 'Pendiente',
	FOREIGN KEY (id_residente) REFERENCES RESIDENTES(id_residente)
)";

if (!mysqli_query($conexion, $sql_tabla_pqrs)) {
	$msg = addslashes(mysqli_error($conexion));
	echo "<script>alert('No se pudo preparar la tabla PQRS: $msg'); window.history.back();</script>";
	exit();
}

$sql = "INSERT INTO PQRS (id_residente, tipo, asunto, descripcion, fecha, estado) VALUES (?, ?, ?, ?, NOW(), 'Pendiente')";
$stmt = $conexion->prepare($sql);

if (!$stmt) {
	echo "<script>alert('Error de base de datos al preparar PQRS.'); window.history.back();</script>";
	exit();
}

$stmt->bind_param('isss', $residente_id, $tipo, $asunto, $descripcion);

if ($stmt->execute()) {
	echo "<script>alert('PQRS registrado exitosamente'); window.location.href='../../html/residente/placeholders/registrar_pqrs.html';</script>";
} else {
	$msg = addslashes($stmt->error);
	echo "<script>alert('Error al registrar PQRS: $msg'); window.history.back();</script>";
}

$stmt->close();
$conexion->close();
?>
