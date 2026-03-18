<?php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_inmueble = $_POST['id_inmueble'] ?? null;
    $tipo_documento = $_POST['tipo_documento'];
    $visibilidad = $_POST['visibilidad'];
    $fecha = date("Y-m-d");

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

        $sql = "INSERT INTO DOCUMENTOS 
        (id_inmueble, tipo_documento, url_documento, fecha_subida, visibilidad)
        VALUES (?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("issss", $id_inmueble, $tipo_documento, $rutaFinal, $fecha, $visibilidad);
        $stmt->execute();

        header("Location: documentos.html"); // vuelve al HTML
    }
}
?>