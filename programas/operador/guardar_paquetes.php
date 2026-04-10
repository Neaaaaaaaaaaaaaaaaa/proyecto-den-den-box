<?php
session_start();
include("../comun/conexion.php");

// Verificar si el usuario esta logueado y es operador o admin
$rol = isset($_SESSION['id_rol']) ? intval($_SESSION['id_rol']) : intval($_SESSION['rol'] ?? 0);
if (!isset($_SESSION['id_usuario']) || ($rol !== 2 && $rol !== 1)) {
    header("Location: ../../html/comun/login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $residente = trim($_POST['residente'] ?? '');
    $empresa = trim($_POST['empresa'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');
    $estado = trim($_POST['estado'] ?? '');

    mysqli_query($conexion, "CREATE TABLE IF NOT EXISTS paquetes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        residente VARCHAR(140) NOT NULL,
        empresa VARCHAR(140) NOT NULL,
        observaciones TEXT NOT NULL,
        estado VARCHAR(60) NOT NULL,
        fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $check_res = mysqli_query($conexion, "SHOW COLUMNS FROM paquetes LIKE 'id_residente'");
    if ($check_res && mysqli_num_rows($check_res) === 0) {
        mysqli_query($conexion, "ALTER TABLE paquetes ADD COLUMN id_residente INT NULL");
    }

    $check_inm = mysqli_query($conexion, "SHOW COLUMNS FROM paquetes LIKE 'id_inmueble'");
    if ($check_inm && mysqli_num_rows($check_inm) === 0) {
        mysqli_query($conexion, "ALTER TABLE paquetes ADD COLUMN id_inmueble INT NULL");
    }

    $id_residente = 0;
    $id_inmueble = 0;
    $residente_canonico = $residente;

    if ($residente !== '') {
        if (preg_match('/^\d+$/', $residente)) {
            $doc = mysqli_real_escape_string($conexion, $residente);
            $sql_buscar = "SELECT r.id_residente, p.nombre_completo
                           FROM RESIDENTES r
                           INNER JOIN PERSONAS p ON p.id_persona = r.id_persona
                           WHERE p.numero_documento = '$doc'
                           LIMIT 1";
        } else {
            $nombre = mysqli_real_escape_string($conexion, $residente);
            $sql_buscar = "SELECT r.id_residente, p.nombre_completo
                           FROM RESIDENTES r
                           INNER JOIN PERSONAS p ON p.id_persona = r.id_persona
                           WHERE LOWER(p.nombre_completo) = LOWER('$nombre')
                              OR LOWER(p.nombre_completo) LIKE LOWER(CONCAT('%', '$nombre', '%'))
                           ORDER BY (LOWER(p.nombre_completo) = LOWER('$nombre')) DESC, p.nombre_completo ASC
                           LIMIT 1";
        }

        $r_buscar = mysqli_query($conexion, $sql_buscar);
        if ($r_buscar && mysqli_num_rows($r_buscar) > 0) {
            $f_res = mysqli_fetch_assoc($r_buscar);
            $id_residente = intval($f_res['id_residente'] ?? 0);
            $residente_canonico = trim((string) ($f_res['nombre_completo'] ?? $residente));
        }
    }

    if ($id_residente > 0) {
        $r_inm = mysqli_query($conexion, "SELECT id_inmueble FROM RESIDENTE_INMUEBLE WHERE id_residente = $id_residente ORDER BY fecha_ingreso DESC LIMIT 1");
        if ($r_inm && mysqli_num_rows($r_inm) > 0) {
            $id_inmueble = intval(mysqli_fetch_assoc($r_inm)['id_inmueble'] ?? 0);
        }
    }

    $id_residente_sql = ($id_residente > 0) ? (string) $id_residente : 'NULL';
    $id_inmueble_sql = ($id_inmueble > 0) ? (string) $id_inmueble : 'NULL';

    // Insertar en la base de datos
    $sql = "INSERT INTO paquetes (residente, empresa, observaciones, estado, id_residente, id_inmueble) VALUES (?, ?, ?, ?, $id_residente_sql, $id_inmueble_sql)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssss", $residente_canonico, $empresa, $observaciones, $estado);

    if ($stmt->execute()) {
        header("Location: ../../html/operador/operator_dashboard.html?paquete=ok");
        exit();
    } else {
        echo "Error al registrar el paquete: " . $conexion->error;
    }

    $stmt->close();
    $conexion->close();
}
?>