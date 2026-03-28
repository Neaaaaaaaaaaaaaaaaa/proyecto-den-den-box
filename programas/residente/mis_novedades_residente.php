<?php
session_start();

$rol = intval($_SESSION['rol'] ?? 0);
if ($rol !== 3) {
    header("Location: ../../html/comun/login.html?error=sesion_expirada");
    exit;
}

$ahora = date('d/m/Y H:i:s');
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="30">
<title>Mis Novedades - Den Den Box</title>
<link rel="shortcut icon" href="../../img/warzone.svg" type="image/x-icon">
<link rel="stylesheet" href="../../css/style.css">
<style>
  .news-page {
    max-width: 1150px;
    margin: 24px auto 40px;
    padding: 0 16px;
  }

  .news-panel {
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    border: 1px solid #dbe7ff;
    border-radius: 14px;
    box-shadow: 0 10px 26px rgba(16, 24, 40, 0.08);
    padding: 20px;
  }

  .news-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 14px;
  }

  .news-head h1 {
    margin: 0;
    color: #1d2939;
  }

  .news-head p {
    margin: 6px 0 0;
    color: #475467;
    font-size: 0.92rem;
  }

  .live-chip {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 999px;
    background: #ecfdf3;
    color: #067647;
    font-weight: 700;
    font-size: 12px;
    border: 1px solid #abefc6;
  }

  .news-frame {
    width: 100%;
    min-height: 540px;
    border: 1px solid #d9e3f0;
    border-radius: 10px;
    background: #fff;
  }

  .news-actions {
    margin-top: 14px;
    text-align: center;
  }
</style>
</head>
<body>
<header class="header">
  <div class="container navbar">
    <a class="brand" href="../../html/residente/index_residente.html">
      <img src="../../img/logo.png" alt="logo">
      <div>
        <div class="title">Den Den Box</div>
        <div class="subtitle">Mis Novedades</div>
      </div>
    </a>
    <nav class="nav-links">
      <a href="../../html/residente/index_residente.html">Inicio</a>
      <a href="dashboard_residente.php">Dashboard residente</a>
      <a href="../../programas/auth/logout.php" class="btn-login">Cerrar Sesion</a>
    </nav>
  </div>
</header>

<main class="news-page">
  <section class="news-panel">
    <div class="news-head">
      <div>
        <h1>Mis Novedades</h1>
        <p>Consulta las novedades relacionadas con tu inmueble, incluyendo actualizaciones de PQRS, pagos y comunicaciones importantes.</p>
    </div>

    <iframe src="listar_mis_novedades_residente.php" class="news-frame" title="Novedades del residente"></iframe>

    <div class="news-actions">
      <a href="dashboard_residente.php" class="buttonplace">Volver</a>
    </div>
  </section>
</main>

<footer class="footer">&copy; 2026 Den Den Box - Proyecto SENA</footer>
</body>
</html>
