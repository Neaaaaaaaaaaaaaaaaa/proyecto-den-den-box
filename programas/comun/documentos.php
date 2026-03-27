<?php
session_start();
include("conexion.php");

$rol = $_SESSION['rol'] ?? null;
$mensaje = "";
if(isset($_GET['status'])){
    if($_GET['status'] === 'deleted') $mensaje = "Documento eliminado correctamente.";
    if($_GET['status'] === 'delete_error') $mensaje = "No se pudo eliminar el documento.";
    if($_GET['status'] === 'unauthorized') $mensaje = "No autorizado para eliminar documentos.";
}

$where = "";

if($rol == 3){
        $id_residente = $_SESSION['id_residente'] ?? 0;
        $inmuebles = [];
        if($id_residente){
                $sql_inm = "SELECT DISTINCT id_inmueble FROM RESIDENTE_INMUEBLE WHERE id_residente = " . intval($id_residente);
                $res_inm = mysqli_query($conexion, $sql_inm);
                while($res_inm && $f = mysqli_fetch_assoc($res_inm)){
                        $inmuebles[] = intval($f['id_inmueble']);
                }
        }
        if(count($inmuebles) > 0){
                $in_list = implode(',', $inmuebles);
                $where = "WHERE (d.visibilidad='global' OR (d.visibilidad='inmueble' AND d.id_inmueble IN ($in_list)))";
        } else {
                $where = "WHERE d.visibilidad='global'";
        }
} elseif($rol == 4){
        $id_propietario = $_SESSION['id_propietario'] ?? 0;
        if(!$id_propietario && isset($_SESSION['id_persona'])){
                $id_persona_s = intval($_SESSION['id_persona']);
                $q_prop = mysqli_query($conexion, "SELECT id_propietario FROM PROPIETARIOS WHERE id_persona=$id_persona_s LIMIT 1");
                if($q_prop && mysqli_num_rows($q_prop) > 0){
                        $id_propietario = intval(mysqli_fetch_assoc($q_prop)['id_propietario']);
                }
        }

        $inmuebles = [];
        if($id_propietario){
                $sql_inm = "SELECT id_inmueble FROM INMUEBLES WHERE id_propietario = " . intval($id_propietario);
                $res_inm = mysqli_query($conexion, $sql_inm);
                while($res_inm && $f = mysqli_fetch_assoc($res_inm)){
                        $inmuebles[] = intval($f['id_inmueble']);
                }
        }
        if(count($inmuebles) > 0){
                $in_list = implode(',', $inmuebles);
                $where = "WHERE (d.visibilidad='global' OR (d.visibilidad='inmueble' AND d.id_inmueble IN ($in_list)))";
        } else {
                $where = "WHERE d.visibilidad='global'";
        }
}

$sql = "SELECT d.*, i.numero AS apartamento, t.nombre AS torre
                FROM DOCUMENTOS d
                LEFT JOIN INMUEBLES i ON i.id_inmueble = d.id_inmueble
                LEFT JOIN TORRES t ON t.id_torre = i.id_torre
                $where
                ORDER BY d.fecha_subida DESC, d.id_documento DESC";

$resultado = mysqli_query($conexion, $sql);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Documentos Publicados</title>
<link rel="stylesheet" href="../../css/style.css">
</head>
<body style="background:#f5f7fb;">
<?php if($mensaje !== ""): ?>
<div style="margin:8px 10px;padding:10px;border-radius:8px;background:#eef6ff;border:1px solid #b9d9ff;color:#0b4f93;">
    <?php echo htmlspecialchars($mensaje); ?>
</div>
<?php endif; ?>

<div class="table-container" style="margin-top:0;">
<table class="residentes-table">
    <thead>
        <tr>
            <th>Tipo</th>
            <th>Visibilidad</th>
            <th>Inmueble</th>
            <th>Fecha</th>
            <th>Archivo</th>
            <?php if(intval($rol) === 1): ?>
            <th>Accion</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
    <?php if($resultado && mysqli_num_rows($resultado) > 0): ?>
        <?php while($fila = mysqli_fetch_assoc($resultado)): ?>
            <?php
                $archivo = basename($fila['url_documento']);
                $inmueble_txt = ($fila['visibilidad'] === 'global')
                    ? 'Todos'
                    : ('ID '.$fila['id_inmueble'].' - '.($fila['torre'] ?? '-').' '.($fila['apartamento'] ?? '-'));
            ?>
            <tr>
                <td><?php echo htmlspecialchars($fila['tipo_documento']); ?></td>
                <td><?php echo htmlspecialchars($fila['visibilidad']); ?></td>
                <td><?php echo htmlspecialchars($inmueble_txt); ?></td>
                <td><?php echo htmlspecialchars($fila['fecha_subida']); ?></td>
                <td><a href="/proyecto-den-den-box/programas/comun/ver_documento.php?f=<?php echo rawurlencode($archivo); ?>" target="_blank">Ver documento</a></td>
                <?php if(intval($rol) === 1): ?>
                <td>
                    <form method="post" action="eliminar_documento.php" onsubmit="return confirm('¿Eliminar este documento?');" style="margin:0;">
                        <input type="hidden" name="id_documento" value="<?php echo intval($fila['id_documento']); ?>">
                        <button type="submit" style="padding:6px 10px;border:none;border-radius:6px;background:#b42318;color:#fff;font-weight:700;cursor:pointer;">Eliminar</button>
                    </form>
                </td>
                <?php endif; ?>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="<?php echo (intval($rol) === 1) ? 6 : 5; ?>">No hay documentos publicados.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
</body>
</html>
