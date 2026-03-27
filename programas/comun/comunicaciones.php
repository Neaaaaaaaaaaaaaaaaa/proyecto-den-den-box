<?php
session_start();

// Validar que esté logueado como Admin o Operador
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] != 1 && $_SESSION['rol'] != 2)) {
  header("Location: ../../html/comun/login.html");
    exit;
}
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Comunicaciones — Admin</title>
<link rel="stylesheet" href="../../css/style.css">
</head>

<body>

<header class="header">
  <div class="container navbar">
    <a class="brand" href="../../html/admin/index_admin.html">
      <img src="../../img/logo.png">
      <div>
        <div class="title">Den Den Box</div>
        <div class="subtitle">Comunicaciones</div>
      </div>
    </a>
  </div>
</header>

<main class="container">

<h1>Publicar Comunicación</h1>

<form action="../../programas/admin/guardar_comunicacion.php" method="POST">

<input type="text" name="titulo" placeholder="Título" class="login-input" required>

<input type="text" name="tipo" placeholder="Tipo (Circular, Aviso...)" class="login-input" required>

<select name="estado" class="login-input">
  <option value="Activa">Activa</option>
  <option value="Vigente">Vigente</option>
  <option value="Prioritario">Prioritario</option>
  <option value="Inactiva">Inactiva</option>
</select>

<label>Destinatario</label>
<select name="destinatario" class="login-input" required>
  <option value="global">Comunicación Global (Todo el conjunto)</option>
  <option value="inmueble">Comunicación Específica (Un inmueble)</option>
</select>

<label>Número de Inmueble (opcional, solo si es específica)</label>
<input type="number" name="num_inmueble" placeholder="Número de inmueble" class="login-input">

<textarea name="contenido" placeholder="Contenido del comunicado" class="login-input" required></textarea>

<button type="submit" class="login-btn">Publicar</button>

</form>

<a href="../../programas/admin/listar_comunicaciones_admin.php" class="buttonplace">
Ver Comunicaciones
</a>
<br>

    <div>

        <a href="../../html/admin/admin_dashboard.html" class="buttonplace">Volver</a>

    </div>
</main>

</body>
</html>
