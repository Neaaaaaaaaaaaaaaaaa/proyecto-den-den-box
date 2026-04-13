<?php
session_start();
include(__DIR__ . '/../comun/conexion.php');

if (!isset($_SESSION['rol']) || ($_SESSION['rol'] != 1 && $_SESSION['rol'] != 2)) {
    header("Location: ../../html/comun/login.html");
    exit;
}

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

// Compatibilidad con bases antiguas: agrega emisor si aún no existe.
$check_sender_col = mysqli_query($conexion, "SHOW COLUMNS FROM COMUNICACIONES LIKE 'id_usuario_emisor'");
if($check_sender_col && mysqli_num_rows($check_sender_col) === 0){
    mysqli_query($conexion, "ALTER TABLE COMUNICACIONES ADD COLUMN id_usuario_emisor INT NULL");
}

$titulo = mysqli_real_escape_string($conexion, $_POST['titulo']);
$tipo = mysqli_real_escape_string($conexion, $_POST['tipo']);
$estado = mysqli_real_escape_string($conexion, $_POST['estado']);
$contenido = mysqli_real_escape_string($conexion, $_POST['contenido']);
$destinatario = $_POST['destinatario'] ?? 'global';

$url_archivo = NULL;
if (isset($_FILES['archivo_comunicacion']) && isset($_FILES['archivo_comunicacion']['error']) && $_FILES['archivo_comunicacion']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['archivo_comunicacion']['error'] !== UPLOAD_ERR_OK) {
        echo "<script>alert('No se pudo cargar el archivo adjunto.');window.history.back();</script>";
        exit;
    }

    $permitidos = ['pdf','doc','docx','jpg','jpeg','png'];
    $nombre_original = $_FILES['archivo_comunicacion']['name'];
    $ext = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
    if (!in_array($ext, $permitidos, true)) {
        echo "<script>alert('Tipo de archivo no permitido. Usa PDF, DOC, DOCX, JPG o PNG.');window.history.back();</script>";
        exit;
    }

    $dir_subida = __DIR__ . '/../uploads/comunicaciones/';
    if (!is_dir($dir_subida)) {
        mkdir($dir_subida, 0777, true);
    }

    $nombre_archivo = 'com_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $ruta_destino = $dir_subida . $nombre_archivo;

    if (!move_uploaded_file($_FILES['archivo_comunicacion']['tmp_name'], $ruta_destino)) {
        echo "<script>alert('No se pudo guardar el archivo adjunto.');window.history.back();</script>";
        exit;
    }

    $url_archivo = 'uploads/comunicaciones/' . $nombre_archivo;
}


$id_inmueble = NULL;
$id_usuario_emisor = intval($_SESSION['id_usuario'] ?? 0);
$id_usuario_emisor_sql = ($id_usuario_emisor > 0) ? (string) $id_usuario_emisor : 'NULL';

if ($destinatario === 'inmueble') {
    $id_inmueble_post = intval($_POST['id_inmueble'] ?? 0);
    $torre = trim($_POST['torre'] ?? '');
    $numero_inmueble = trim($_POST['numero_inmueble'] ?? '');

    if ($id_inmueble_post > 0) {
        $sql_buscar = "SELECT id_inmueble FROM INMUEBLES WHERE id_inmueble=$id_inmueble_post LIMIT 1";
        $resultado_buscar = mysqli_query($conexion, $sql_buscar);
        if ($resultado_buscar && mysqli_num_rows($resultado_buscar) > 0) {
            $fila = mysqli_fetch_assoc($resultado_buscar);
            $id_inmueble = intval($fila['id_inmueble']);
        }
    }

    if ($id_inmueble === NULL && ($torre === '' || $numero_inmueble === '')) {
        echo "<script>
        alert('Debes seleccionar torre y apartamento para comunicación específica');
        window.history.back();
        </script>";
        exit;
    }

    if ($id_inmueble === NULL) {
        $torre_sql = mysqli_real_escape_string($conexion, $torre);
        $numero_sql = mysqli_real_escape_string($conexion, $numero_inmueble);
        $sql_buscar = "SELECT i.id_inmueble
                       FROM INMUEBLES i
                       INNER JOIN TORRES t ON i.id_torre = t.id_torre
                       WHERE t.nombre='$torre_sql' AND i.numero='$numero_sql'
                       LIMIT 1";
        $resultado_buscar = mysqli_query($conexion, $sql_buscar);

        if ($resultado_buscar && mysqli_num_rows($resultado_buscar) > 0) {
            $fila = mysqli_fetch_assoc($resultado_buscar);
            $id_inmueble = intval($fila['id_inmueble']);
        }
    }

    if ($id_inmueble === NULL) {
        echo "<script>
        alert('No se encontró el inmueble con la torre y apartamento seleccionados');
        window.history.back();
        </script>";
        exit;
    }
}

if ($url_archivo === NULL) {
    $url_archivo_sql = 'NULL';
} else {
    $url_archivo_sql = "'" . mysqli_real_escape_string($conexion, $url_archivo) . "'";
}

if ($id_inmueble === NULL) {
    $sql = "INSERT INTO COMUNICACIONES (titulo, tipo, estado, contenido, fecha, id_inmueble, url_archivo, id_usuario_emisor) VALUES ('$titulo','$tipo','$estado','$contenido',NOW(),NULL,$url_archivo_sql,$id_usuario_emisor_sql)";
} else {
    $sql = "INSERT INTO COMUNICACIONES (titulo, tipo, estado, contenido, fecha, id_inmueble, url_archivo, id_usuario_emisor) VALUES ('$titulo','$tipo','$estado','$contenido',NOW(),'$id_inmueble',$url_archivo_sql,$id_usuario_emisor_sql)";
}

mysqli_query($conexion, $sql);

header("Location: ../../html/admin/placeholders/comunicaciones.html");
exit;
?>