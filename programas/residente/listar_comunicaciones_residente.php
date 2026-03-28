<?php
session_start();
include("../comun/conexion.php");

$rol = $_SESSION['rol'] ?? null;
$id_persona = $_SESSION['id_persona'] ?? null;

// Compatibilidad con bases antiguas: agrega id_inmueble si aún no existe.
$check_col = mysqli_query($conexion, "SHOW COLUMNS FROM COMUNICACIONES LIKE 'id_inmueble'");
if($check_col && mysqli_num_rows($check_col) === 0){
    mysqli_query($conexion, "ALTER TABLE COMUNICACIONES ADD COLUMN id_inmueble INT NULL");
}

// Compatibilidad con bases antiguas: agrega url_archivo si aún no existe.
$check_file_col = mysqli_query($conexion, "SHOW COLUMNS FROM COMUNICACIONES LIKE 'url_archivo'");
if($check_file_col && mysqli_num_rows($check_file_col) === 0){
    mysqli_query($conexion, "ALTER TABLE COMUNICACIONES ADD COLUMN url_archivo VARCHAR(255) NULL");
}

// Solo Residente y Propietario pueden acceder
if ($rol === null || ($rol != 3 && $rol != 4)) {
    header("Location: ../../html/comun/login.html?error=sesion_expirada");
    exit;
}

$historial = isset($_GET['historial']) && $_GET['historial'] === '1';
$embed = isset($_GET['embed']) && $_GET['embed'] === '1';

// Obtener los inmuebles del usuario
if ($rol == 3) {
    // Para residentes: obtener inmuebles desde RESIDENTE_INMUEBLE
    $sql_inmuebles = "SELECT DISTINCT i.id_inmueble FROM INMUEBLES i 
                     JOIN RESIDENTE_INMUEBLE ri ON i.id_inmueble = ri.id_inmueble 
                     JOIN RESIDENTES r ON ri.id_residente = r.id_residente 
                     WHERE r.id_persona='$id_persona'";
} else if ($rol == 4) {
    // Para propietarios: obtener inmuebles donde es propietario
    $sql_inmuebles = "SELECT id_inmueble FROM INMUEBLES WHERE id_propietario = (SELECT id_propietario FROM PROPIETARIOS WHERE id_persona='$id_persona')";
}

$resultado_inmuebles = mysqli_query($conexion, $sql_inmuebles);
$ids_inmuebles = [];

while ($row = mysqli_fetch_assoc($resultado_inmuebles)) {
    $ids_inmuebles[] = $row['id_inmueble'];
}

// Crear query para mostrar comunicaciones globales o especificadas para sus inmuebles
if (count($ids_inmuebles) > 0) {
    $ids_string = implode(',', $ids_inmuebles);
    if ($historial) {
        $sql = "SELECT c.*, i.numero as numero_inmueble FROM COMUNICACIONES c 
               LEFT JOIN INMUEBLES i ON c.id_inmueble = i.id_inmueble 
               WHERE c.id_inmueble IS NULL OR c.id_inmueble IN ($ids_string) 
               ORDER BY c.fecha DESC";
    } else {
        $sql = "SELECT c.*, i.numero as numero_inmueble FROM COMUNICACIONES c 
               LEFT JOIN INMUEBLES i ON c.id_inmueble = i.id_inmueble 
               WHERE (c.id_inmueble IS NULL OR c.id_inmueble IN ($ids_string)) 
               AND c.estado IN ('Activa','Vigente','Prioritario') 
               ORDER BY c.fecha DESC";
    }
} else {
    // Si no tiene inmuebles, solo mostrar comunicaciones globales
    if ($historial) {
        $sql = "SELECT c.*, i.numero as numero_inmueble FROM COMUNICACIONES c 
               LEFT JOIN INMUEBLES i ON c.id_inmueble = i.id_inmueble 
               WHERE c.id_inmueble IS NULL 
               ORDER BY c.fecha DESC";
    } else {
        $sql = "SELECT c.*, i.numero as numero_inmueble FROM COMUNICACIONES c 
               LEFT JOIN INMUEBLES i ON c.id_inmueble = i.id_inmueble 
               WHERE c.id_inmueble IS NULL 
               AND c.estado IN ('Activa','Vigente','Prioritario') 
               ORDER BY c.fecha DESC";
    }
}

$resultado = mysqli_query($conexion, $sql);
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Comunicaciones</title>
<link rel="shortcut icon" href="../../../img/warzone.svg" type="image/x-icon">
<link rel="stylesheet" href="../../css/style.css">
<style>
    .comunicaciones-page {
        max-width: 980px;
        margin: 32px auto 40px;
        padding: 0 16px;
    }

    .comunicaciones-panel {
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid #d6e4ff;
        border-radius: 16px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        padding: 24px;
    }

    .comunicaciones-header {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .comunicaciones-header h1 {
        margin: 0;
        font-size: 1.5rem;
        color: #1f2937;
    }

    .comunicaciones-header p {
        margin: 6px 0 0;
        color: #475569;
        font-size: 0.95rem;
    }

    .comunicaciones-actions {
        margin-bottom: 16px;
    }

    .comunicaciones-page .cards {
        margin-top: 0;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        align-items: stretch;
    }

    .comunicaciones-page .card {
        text-align: left;
        color: #1f2937;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 22px rgba(30, 64, 175, 0.08);
    }

    .comunicaciones-page .card p {
        margin-top: 10px;
        margin-bottom: 0;
        line-height: 1.55;
        font-weight: 500;
        color: #334155;
    }

    .comunicaciones-page .card-meta {
        margin: 8px 0;
        color: #334155;
        font-size: 0.92rem;
        line-height: 1.55;
    }

    .comunicaciones-empty {
        text-align: center;
        padding: 26px;
        border: 1px dashed #bfdbfe;
        background: #eff6ff;
        color: #1e3a8a;
        font-weight: 600;
    }

    .comunicaciones-back {
        margin-top: 18px;
        text-align: center;
    }

    @media (max-width: 640px) {
        .comunicaciones-panel {
            padding: 18px;
        }

        .comunicaciones-header h1 {
            font-size: 1.2rem;
        }

        .comunicaciones-page .cards {
            grid-template-columns: 1fr;
        }
    }
</style>
</head>

<body>
<header class="header">
  <div class="container navbar">
    <a class="brand" href="../../html/residente/index_residente.html">
      <img src="../../img/logo.png">
      <div>
        <div class="title">Den Den Box</div>
        <div class="subtitle">Comunicaciones</div>
      </div>
    </a>
    <nav class="nav-links">
        <a href="../index_residente.html">Inicio</a>
        
      <a href="../../../programas/auth/logout.php" class="btn-login">Cerrar Sesión</a>
    </nav>
  </div>
</header>

<main class="container comunicaciones-page">
<section class="comunicaciones-panel">

<div class="comunicaciones-header">
    <div>
        <h1>Comunicaciones del Conjunto</h1>
        <p>Consulta avisos globales y mensajes asociados a tus inmuebles.</p>
    </div>
</div>



<?php if (!$embed): ?>
<div class="comunicaciones-actions">
    <?php if ($historial): ?>
        <a href="listar_comunicaciones_residente.php" class="buttonplace">Ver sólo vigentes</a>
    <?php else: ?>
        <a href="listar_comunicaciones_residente.php?historial=1" class="buttonplace">Ver historial completo</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="cards">

<?php if (mysqli_num_rows($resultado) === 0): ?>
    <div class="card comunicaciones-empty">No hay comunicaciones disponibles.</div>
<?php else: ?>
    <?php while ($row = mysqli_fetch_assoc($resultado)): ?>
        <div class="card">
            <?php if (!empty($row['url_archivo'])): ?>
                <a href="../<?php echo htmlspecialchars($row['url_archivo']); ?>" target="_blank" style="font-weight:700;text-decoration:none;color:inherit;">
                    <?php echo htmlspecialchars($row['titulo']); ?>
                </a><br>
            <?php else: ?>
                <strong><?php echo htmlspecialchars($row['titulo']); ?></strong><br>
            <?php endif; ?>
                        <div class="card-meta">
                            Publicado: <?php echo $row['fecha']; ?><br>
                            Tipo: <?php echo htmlspecialchars($row['tipo']); ?><br>
                        </div>
            <?php if (is_null($row['id_inmueble'])): ?>
                <span style="background-color:#4CAF50;color:white;padding:4px 8px;border-radius:4px;font-size:12px;">Comunicación Global</span>
            <?php else: ?>
                <span style="background-color:#2196F3;color:white;padding:4px 8px;border-radius:4px;font-size:12px;">Inmueble <?php echo htmlspecialchars($row['numero_inmueble']); ?></span>
            <?php endif; ?>
            <br>
            Estado:
            <span class="tag <?php
                if ($row['estado'] == "Activa") echo "tag-activa";
                if ($row['estado'] == "Vigente") echo "tag-vigente";
                if ($row['estado'] == "Prioritario") echo "tag-prioritario";
                if ($row['estado'] == "Inactiva") echo "tag-inactiva";
            ?>">
                <?php echo htmlspecialchars($row['estado']); ?>
            </span>
            <p><?php echo nl2br(htmlspecialchars($row['contenido'])); ?></p>
            <?php if (!empty($row['url_archivo'])): ?>
                <a href="../<?php echo htmlspecialchars($row['url_archivo']); ?>" target="_blank" class="buttonplace" style="margin-top:8px;">Ver archivo adjunto</a>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php endif; ?>

</div>

</section>

<?php if (!$embed): ?>
<div class="comunicaciones-back">
    <a href="../../html/residente/placeholders/ver_comunicaciones.html" class="buttonplace">Volver</a>
</div>
<?php endif; ?>

</main>
<footer class="footer mt-60">© 2025 Den Den Box — Proyecto SENA</footer>
</body>
</html>
