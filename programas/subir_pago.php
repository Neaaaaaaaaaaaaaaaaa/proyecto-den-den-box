<?php
session_start();
include("conexion.php");

$id_inmueble_sql = isset($_SESSION['id_inmueble']) ? intval($_SESSION['id_inmueble']) : 'NULL';

if(isset($_POST['nombre'], $_POST['descripcion'], $_POST['fecha_pago'], $_POST['valor'], $_POST['metodo_pago'], $_POST['estado_pago'], $_FILES['archivo'])){

    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $fecha_pago = $_POST['fecha_pago'];
    $valor = $_POST['valor'];
    $metodo_pago = $_POST['metodo_pago'];
    $estado_pago = $_POST['estado_pago'];

    $archivo = $_FILES['archivo']['name'];
    $ruta = "../uploads/" . $archivo;

    move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta);

    $sql = "INSERT INTO pagos(id_inmueble, nombre, descripcion, archivo, fecha_pago, valor, metodo_pago, estado_pago)
            VALUES($id_inmueble_sql,'$nombre','$descripcion','$archivo','$fecha_pago','$valor','$metodo_pago','$estado_pago')";

    if(mysqli_query($conexion, $sql)){
        $respuesta = "ok";
    } else {
        $respuesta = "Error en la base de datos: " . mysqli_error($conexion);
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
  <link rel="shortcut icon" href="../img/warzone.svg" type="image/x-icon">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="header">
  <div class="container navbar">
    <a class="brand" href="index.html">
      <img src="../img/logo.png">
      <div>
        <div class="title">Den Den Box</div>
        <div class="subtitle">Registrar pago</div>
      </div>
    </a>
    <nav class="nav-links">
      <a href="../html/index_residente.html">Inicio</a>
      <a href="../html/user_dashboard.html">Dashboard residente</a>
      <a href="../html/login.html" class="btn-login">Cerrar Sesión</a>
    </nav>
  </div>
</header>

<main class="container" style="padding-top:28px;">
  <div style="margin-top:20px;max-width:600px;background:white;padding:20px;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,0.06);text-align:center;">
    <?php if($respuesta === "ok"): ?>
      <h2 style="color:green;margin-bottom:20px;">✓ Pago subido correctamente</h2>
      <p style="margin-bottom:20px;color:#666;">Tu pago ha sido registrado en el sistema.</p>
      <a href="../html/user_dashboard.html" style="display:inline-block;background:var(--primary);color:white;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:700;">Volver al Dashboard</a>
    <?php else: ?>
      <h2 style="color:red;margin-bottom:20px;">✗ Error al registrar el pago</h2>
      <p style="margin-bottom:20px;color:#666;"><?php echo $respuesta; ?></p>
      <a href="../html/placeholders/registrar_pagos.html" style="display:inline-block;background:var(--primary);color:white;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:700;">Volver al formulario</a>
    <?php endif; ?>
  </div>
  <section class="footer">© 2025 Den Den Box — Proyecto SENA</section>
</main>

</body>
</html>