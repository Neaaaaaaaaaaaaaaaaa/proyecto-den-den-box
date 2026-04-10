<?php
include(__DIR__ . '/../comun/conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_inmueble = trim($_POST['id_inmueble'] ?? '');
    $tipo_documento = trim($_POST['tipo_documento'] ?? '');
    $fecha = date("Y-m-d");

    // Regla de negocio: si hay apartamento seleccionado => visibilidad por inmueble, si no => global.
    $visibilidad = ($id_inmueble !== '') ? 'inmueble' : 'global';

    if($tipo_documento === ''){
        header("Location: /proyecto-den-den-box/html/admin/placeholders/documentos.html?status=invalid");
        exit();
    }

    if($visibilidad === 'global'){
        $id_inmueble = null;
    }

    // Guardar en /documentos del proyecto (ruta publica correcta).
    $carpeta = __DIR__ . "/../../documentos/";

    if (!file_exists($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

    if(!isset($_FILES['documento']) || $_FILES['documento']['error'] !== UPLOAD_ERR_OK){
        header("Location: /proyecto-den-den-box/html/admin/placeholders/documentos.html?status=upload_error");
        exit();
    }

    $nombreArchivo = $_FILES['documento']['name'];
    $rutaTemporal = $_FILES['documento']['tmp_name'];

    $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
    $permitidos = ["pdf", "doc", "docx", "jpg", "png"];

    if (!in_array($extension, $permitidos)) {
        header("Location: /proyecto-den-den-box/html/admin/placeholders/documentos.html?status=filetype");
        exit();
    }

    if ($_FILES['documento']['size'] > 5000000) {
        header("Location: /proyecto-den-den-box/html/admin/placeholders/documentos.html?status=filesize");
        exit();
    }

    $nombreFinal = time() . "_" . preg_replace('/[^A-Za-z0-9._-]/', '_', $nombreArchivo);
    $rutaFinal = $carpeta . $nombreFinal;

    if (move_uploaded_file($rutaTemporal, $rutaFinal)) {

        if($visibilidad === 'inmueble' && empty($id_inmueble)){
            header("Location: /proyecto-den-den-box/html/admin/placeholders/documentos.html?status=need_inmueble");
            exit();
        }

        if($visibilidad === 'inmueble'){
            $id_inmueble_val = intval($id_inmueble);
                        $q_inm = mysqli_query($conexion, "SELECT i.id_inmueble
                                                                                            FROM INMUEBLES i
                                                                                            WHERE i.id_inmueble=$id_inmueble_val
                                                                                            LIMIT 1");
            if(!$q_inm || mysqli_num_rows($q_inm) === 0){
                header("Location: /proyecto-den-den-box/html/admin/placeholders/documentos.html?status=inmueble_not_found");
                exit();
            }
        }

        $id_inmueble_sql = ($id_inmueble === null || $id_inmueble === '') ? 'NULL' : intval($id_inmueble);
        $tipo_documento_sql = mysqli_real_escape_string($conexion, $tipo_documento);
        $visibilidad_sql = mysqli_real_escape_string($conexion, $visibilidad);

        $sql = "INSERT INTO DOCUMENTOS 
        (id_inmueble, tipo_documento, url_documento, fecha_subida, visibilidad)
        VALUES ($id_inmueble_sql, '$tipo_documento_sql', '$nombreFinal', '$fecha', '$visibilidad_sql')";

        if(!mysqli_query($conexion, $sql)){
            header("Location: /proyecto-den-den-box/html/admin/placeholders/documentos.html?status=db_error");
            exit();
        }

        header("Location: /proyecto-den-den-box/html/admin/placeholders/documentos.html?status=ok");
        exit();
    }

    header("Location: /proyecto-den-den-box/html/admin/placeholders/documentos.html?status=upload_error");
    exit();
}
?>