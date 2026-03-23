<?php
session_start();
include("conexion.php");

// Verificar login
if (!isset($_SESSION['id_usuario'])) {
    header("Location:../html/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_inmueble = isset($_SESSION['id_inmueble']) ? $_SESSION['id_inmueble'] : 1;

// Cargar datos desde la base de datos para el dashboard del residente
$sql_novedades = "SELECT n.id_novedad, n.descripcion, n.fecha_reporte, e.nombre_estado
                  FROM NOVEDAD n
                  JOIN ESTADOS_DE_NOVEDAD e ON n.id_estado = e.id_estado
                  WHERE n.id_usuario = $id_usuario AND n.id_estado != 4
                  ORDER BY n.fecha_reporte DESC LIMIT 3";
$result_novedades = mysqli_query($conexion, $sql_novedades);
$novedades = mysqli_fetch_all($result_novedades, MYSQLI_ASSOC);

$sql_pagos = "SELECT fecha_pago, valor, descripcion FROM PAGOS
              WHERE id_inmueble = $id_inmueble AND estado_pago = 'Pendiente'
              ORDER BY fecha_pago DESC LIMIT 3";
$result_pagos = mysqli_query($conexion, $sql_pagos);
$pagos = mysqli_fetch_all($result_pagos, MYSQLI_ASSOC);

$sql_docs = "SELECT tipo_documento, fecha_subida FROM DOCUMENTOS
             WHERE (id_inmueble = $id_inmueble OR visibilidad = 'global')
             ORDER BY fecha_subida DESC LIMIT 3";
$result_docs = mysqli_query($conexion, $sql_docs);
$documentos = mysqli_fetch_all($result_docs, MYSQLI_ASSOC);

$sql_notif = "SELECT mensaje, tipo, fecha_envio FROM NOTIFICACIONES
              WHERE id_usuario = $id_usuario ORDER BY fecha_envio DESC LIMIT 10";
$result_notif = mysqli_query($conexion, $sql_notif);
$notificaciones = mysqli_fetch_all($result_notif, MYSQLI_ASSOC);

// Mostrar notificaciones
if (count($notificaciones) > 0) {
    foreach ($notificaciones as $notif) {
        echo "<div style='padding:10px; margin:5px 0; background:#fff; border:1px solid #ddd; border-radius:4px;'>";
        echo "<strong>{$notif['tipo']}:</strong> {$notif['mensaje']} <small>({$notif['fecha_envio']})</small>";
        echo "</div>";
    }
} else {
    echo "<p>No hay notificaciones recientes.</p>";
}
?>
