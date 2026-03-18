<?php

session_start();
include("conexion.php");

$rol = $_SESSION['rol'] ?? null;
$where = "";

if($rol == 3){
    $id_residente = $_SESSION['id_residente'] ?? 0;
    $inmuebles = [];
    if($id_residente){
        $sql_inm = "SELECT DISTINCT id_inmueble FROM RESIDENTE_INMUEBLE WHERE id_residente = $id_residente";
        $res_inm = mysqli_query($conexion, $sql_inm);
        while($f = mysqli_fetch_assoc($res_inm)){
            $inmuebles[] = intval($f['id_inmueble']);
        }
    }
    if(count($inmuebles) > 0){
        $in_list = implode(',', $inmuebles);
        $where = "WHERE (visibilidad='global' OR (visibilidad='inmueble' AND id_inmueble IN ($in_list)))";
    } else {
        $where = "WHERE visibilidad='global'";
    }
} elseif($rol == 4){
    $id_propietario = $_SESSION['id_propietario'] ?? 0;
    $inmuebles = [];
    if($id_propietario){
        $sql_inm = "SELECT id_inmueble FROM INMUEBLES WHERE id_propietario = $id_propietario";
        $res_inm = mysqli_query($conexion, $sql_inm);
        while($f = mysqli_fetch_assoc($res_inm)){
            $inmuebles[] = intval($f['id_inmueble']);
        }
    }
    if(count($inmuebles) > 0){
        $in_list = implode(',', $inmuebles);
        $where = "WHERE (visibilidad='global' OR (visibilidad='inmueble' AND id_inmueble IN ($in_list)))";
    } else {
        $where = "WHERE visibilidad='global'";
    }
} else {
    // Administrador, operador y no autenticado ven todo (o solo global según preferencia)
    $where = "";
}

$sql = "SELECT * FROM DOCUMENTOS $where ORDER BY fecha_subida DESC";
$resultado = mysqli_query($conexion,$sql);

while($fila = mysqli_fetch_assoc($resultado)){
    echo "<div style='background:white;padding:15px;margin:10px;border-radius:10px;'>";
    echo "<strong>".$fila['tipo_documento']."</strong><br><br>";
    echo "<a href='/proyecto-den-den-box/documentos/".$fila['url_documento']. "' target='_blank'>Ver documento </a><br><br>";
    echo "<small>Visibilidad: ".$fila['visibilidad']."</small><br>";
    if($fila['visibilidad'] === 'inmueble'){
        echo "<small>Inmueble: ".$fila['id_inmueble']."</small><br>";
    }
    echo "Fecha: ".$fila['fecha_subida'];
    echo "</div>";
}

?>