<?php
session_start();

// Validar que esté logueado
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] != 3 && $_SESSION['rol'] != 4)) {
    header("Location: ../login.html");
    exit;
}
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Comunicaciones</title>
<link rel="stylesheet" href="../../css/style.css">
</head>

<body>

<div class="container">

<h1>Comunicaciones del Conjunto</h1>

<p>Consulta las comunicaciones publicadas por el administrador. Verás tanto comunicaciones globales como las dirigidas específicamente a tu inmueble.</p>

<div style="margin-bottom:16px;">
  <a href="../../programas/listar_comunicaciones_residente.php" class="buttonplace">Ver comunicaciones vigentes</a>
  <a href="../../programas/listar_comunicaciones_residente.php?historial=1" class="buttonplace">Ver historial completo</a>
</div>

<a href="../index_residente.html" class="buttonplace">Volver al dashboard</a>

</div>

</body>
</html>
