<?php
session_start();
include("conexion.php");

$rol = $_SESSION['rol'] ?? null;
$id_persona = $_SESSION['id_persona'] ?? null;

if ($rol === null || !in_array(intval($rol), [1, 2, 3, 4], true)) {
    header("Location: ../../html/comun/login.html");
    exit;
}

// Compatibilidad con bases antiguas.
$check_col = mysqli_query($conexion, "SHOW COLUMNS FROM COMUNICACIONES LIKE 'id_inmueble'");
if($check_col && mysqli_num_rows($check_col) === 0){
        mysqli_query($conexion, "ALTER TABLE COMUNICACIONES ADD COLUMN id_inmueble INT NULL");
}
$check_file_col = mysqli_query($conexion, "SHOW COLUMNS FROM COMUNICACIONES LIKE 'url_archivo'");
if($check_file_col && mysqli_num_rows($check_file_col) === 0){
        mysqli_query($conexion, "ALTER TABLE COMUNICACIONES ADD COLUMN url_archivo VARCHAR(255) NULL");
}

$historial = isset($_GET['historial']) && $_GET['historial'] === '1';
$embed = isset($_GET['embed']) && $_GET['embed'] === '1';

if(intval($rol) === 1 || intval($rol) === 2){
        if($historial){
                $sql = "SELECT c.*, i.numero AS numero_inmueble
                                FROM COMUNICACIONES c
                                LEFT JOIN INMUEBLES i ON c.id_inmueble = i.id_inmueble
                                ORDER BY c.fecha DESC";
        } else {
                $sql = "SELECT c.*, i.numero AS numero_inmueble
                                FROM COMUNICACIONES c
                                LEFT JOIN INMUEBLES i ON c.id_inmueble = i.id_inmueble
                                WHERE c.estado IN ('Activa','Vigente','Prioritario')
                                ORDER BY c.fecha DESC";
        }
} else {
        $ids_inmuebles = [];
        if(intval($rol) === 3){
                $sql_inmuebles = "SELECT DISTINCT i.id_inmueble
                                                    FROM INMUEBLES i
                                                    INNER JOIN RESIDENTE_INMUEBLE ri ON i.id_inmueble = ri.id_inmueble
                                                    INNER JOIN RESIDENTES r ON ri.id_residente = r.id_residente
                                                    WHERE r.id_persona='" . mysqli_real_escape_string($conexion, (string)$id_persona) . "'";
        } else {
                $sql_inmuebles = "SELECT id_inmueble
                                                    FROM INMUEBLES
                                                    WHERE id_propietario = (
                                                        SELECT id_propietario FROM PROPIETARIOS WHERE id_persona='" . mysqli_real_escape_string($conexion, (string)$id_persona) . "'
                                                    )";
        }

        $resultado_inmuebles = mysqli_query($conexion, $sql_inmuebles);
        if($resultado_inmuebles){
                while($row_inm = mysqli_fetch_assoc($resultado_inmuebles)){
                        $ids_inmuebles[] = intval($row_inm['id_inmueble']);
                }
        }

        if(count($ids_inmuebles) > 0){
                $ids_string = implode(',', $ids_inmuebles);
                if($historial){
                        $sql = "SELECT c.*, i.numero AS numero_inmueble
                                        FROM COMUNICACIONES c
                                        LEFT JOIN INMUEBLES i ON c.id_inmueble = i.id_inmueble
                                        WHERE c.id_inmueble IS NULL OR c.id_inmueble IN ($ids_string)
                                        ORDER BY c.fecha DESC";
                } else {
                        $sql = "SELECT c.*, i.numero AS numero_inmueble
                                        FROM COMUNICACIONES c
                                        LEFT JOIN INMUEBLES i ON c.id_inmueble = i.id_inmueble
                                        WHERE (c.id_inmueble IS NULL OR c.id_inmueble IN ($ids_string))
                                            AND c.estado IN ('Activa','Vigente','Prioritario')
                                        ORDER BY c.fecha DESC";
                }
        } else {
                if($historial){
                        $sql = "SELECT c.*, i.numero AS numero_inmueble
                                        FROM COMUNICACIONES c
                                        LEFT JOIN INMUEBLES i ON c.id_inmueble = i.id_inmueble
                                        WHERE c.id_inmueble IS NULL
                                        ORDER BY c.fecha DESC";
                } else {
                        $sql = "SELECT c.*, i.numero AS numero_inmueble
                                        FROM COMUNICACIONES c
                                        LEFT JOIN INMUEBLES i ON c.id_inmueble = i.id_inmueble
                                        WHERE c.id_inmueble IS NULL
                                            AND c.estado IN ('Activa','Vigente','Prioritario')
                                        ORDER BY c.fecha DESC";
                }
        }
}

$resultado = mysqli_query($conexion, $sql);
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
<h1>Comunicaciones</h1>

<?php if (!$embed): ?>
<div style="margin-bottom:16px;">
    <?php if ($historial): ?>
        <a href="listar_comunicaciones.php" class="buttonplace">Ver solo vigentes</a>
    <?php else: ?>
        <a href="listar_comunicaciones.php?historial=1" class="buttonplace">Ver historial completo</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="cards">
<?php if(!$resultado || mysqli_num_rows($resultado) === 0): ?>
    <div class="card">No hay comunicaciones disponibles.</div>
<?php else: ?>
    <?php while($row = mysqli_fetch_assoc($resultado)): ?>
        <div class="card">
            <?php if (!empty($row['url_archivo'])): ?>
                <a href="../<?php echo htmlspecialchars($row['url_archivo']); ?>" target="_blank" style="font-weight:700;text-decoration:none;color:inherit;">
                    <?php echo htmlspecialchars($row['titulo']); ?>
                </a><br>
            <?php else: ?>
                <strong><?php echo htmlspecialchars($row['titulo']); ?></strong><br>
            <?php endif; ?>

            Publicado: <?php echo htmlspecialchars($row['fecha']); ?><br>
            Tipo: <?php echo htmlspecialchars($row['tipo']); ?><br>

            <?php if (is_null($row['id_inmueble'])): ?>
                <span style="background-color:#4CAF50;color:white;padding:4px 8px;border-radius:4px;font-size:12px;">Comunicacion Global</span>
            <?php else: ?>
                <span style="background-color:#2196F3;color:white;padding:4px 8px;border-radius:4px;font-size:12px;">Inmueble <?php echo htmlspecialchars((string)$row['numero_inmueble']); ?></span>
            <?php endif; ?>

            <br>
            Estado:
            <span class="tag <?php
                if ($row['estado'] === 'Activa') echo 'tag-activa';
                if ($row['estado'] === 'Vigente') echo 'tag-vigente';
                if ($row['estado'] === 'Prioritario') echo 'tag-prioritario';
                if ($row['estado'] === 'Inactiva') echo 'tag-inactiva';
            ?>"><?php echo htmlspecialchars($row['estado']); ?></span>

            <p><?php echo nl2br(htmlspecialchars($row['contenido'])); ?></p>

            <?php if (!empty($row['url_archivo'])): ?>
                <a href="../<?php echo htmlspecialchars($row['url_archivo']); ?>" target="_blank" class="buttonplace" style="margin-top:8px;">Ver archivo adjunto</a>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php endif; ?>
</div>

<?php if (!$embed): ?>
    <br>
    <?php if (intval($rol) === 1 || intval($rol) === 2): ?>
        <a href="../../html/admin/placeholders/comunicaciones.html" class="buttonplace">Volver</a>
    <?php elseif (intval($rol) === 4): ?>
        <a href="../../html/propietario/placeholders/ver_comunicaciones_propietario.html" class="buttonplace">Volver</a>
    <?php else: ?>
        <a href="../../html/residente/placeholders/ver_comunicaciones.html" class="buttonplace">Volver</a>
    <?php endif; ?>
<?php endif; ?>

</div>
</body>
</html>
