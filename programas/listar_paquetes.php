<?php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado y es operador o admin
if (!isset($_SESSION['id_usuario']) || ($_SESSION['id_rol'] != 2 && $_SESSION['id_rol'] != 1)) {
    header("Location: ../html/login.html");
    exit();
}

// Obtener todos los paquetes
$sql = "SELECT id, residente, empresa, observaciones, estado, fecha_registro FROM paquetes ORDER BY fecha_registro DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Paquetes</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Historial de Paquetes Registrados</h1>
    <table class="tabla-dashboard">
        <thead>
            <tr>
                <th>ID</th>
                <th>Residente</th>
                <th>Empresa</th>
                <th>Observaciones</th>
                <th>Estado</th>
                <th>Fecha Registro</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["id"] . "</td>";
                    echo "<td>" . $row["residente"] . "</td>";
                    echo "<td>" . $row["empresa"] . "</td>";
                    echo "<td>" . $row["observaciones"] . "</td>";
                    echo "<td>" . $row["estado"] . "</td>";
                    echo "<td>" . $row["fecha_registro"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No hay paquetes registrados.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <?php
    $conn->close();
    ?>
</body>
</html>