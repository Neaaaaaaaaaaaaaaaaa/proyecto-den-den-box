<?php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_inmueble = trim($_POST['id_inmueble'] ?? '');
    $tipo_documento = trim($_POST['tipo_documento']);
    $visibilidad = trim($_POST['visibilidad']);
    $fecha = date("Y-m-d");

    if($visibilidad === 'global'){
        $id_inmueble = null;
    }

    $carpeta = "../documentos/";

    if (!file_exists($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

    $nombreArchivo = $_FILES['documento']['name'];
    $rutaTemporal = $_FILES['documento']['tmp_name'];

    $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
    $permitidos = ["pdf", "doc", "docx", "jpg", "png"];

    if (!in_array($extension, $permitidos)) {
        die("Archivo no permitido");
    }

    if ($_FILES['documento']['size'] > 5000000) {
        die("Archivo muy grande");
    }

    $nombreFinal = time() . "_" . $nombreArchivo;
    $rutaFinal = $carpeta . $nombreFinal;

    if (move_uploaded_file($rutaTemporal, $rutaFinal)) {

        // validar visibilidad y clave inmobiliaria
        if($visibilidad === 'inmueble' && empty($id_inmueble)){
            die("Para visibilidad 'inmueble' el campo ID Inmueble es obligatorio");
        }

        $id_inmueble_sql = ($id_inmueble === null || $id_inmueble === '') ? 'NULL' : intval($id_inmueble);

        $sql = "INSERT INTO DOCUMENTOS 
        (id_inmueble, tipo_documento, url_documento, fecha_subida, visibilidad)
        VALUES ($id_inmueble_sql, '$tipo_documento', '$rutaFinal', '$fecha', '$visibilidad')";

        mysqli_query($conexion, $sql);

        header("Location: /proyecto-den-den-box/html/placeholders/documentos.html");
        exit();
    }
}
?>