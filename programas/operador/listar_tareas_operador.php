<?php
session_start();
include("../comun/conexion.php");

// Verificar si el usuario está logueado y es operador (rol 2) o admin (rol 1)
$rol = isset($_SESSION['id_rol']) ? intval($_SESSION['id_rol']) : intval($_SESSION['rol'] ?? 0);
if (!isset($_SESSION['id_usuario']) || ($rol !== 2 && $rol !== 1)) {
    header("Location: ../../html/comun/login.html");
    exit();
}

// Obtener filtro de estado (si existe)
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : 'todos';

// Construir la consulta SQL
$sql = "SELECT id, titulo, descripcion, estado, fecha_creacion, fecha_vencimiento, prioridad FROM tareas WHERE rol = 'Operador'";

if ($filtro_estado !== 'todos') {
    $sql .= " AND estado = '" . $conexion->real_escape_string($filtro_estado) . "'";
}

$sql .= " ORDER BY 
    CASE 
        WHEN prioridad = 'Urgente' THEN 1
        WHEN prioridad = 'Alta' THEN 2
        WHEN prioridad = 'Media' THEN 3
        ELSE 4
    END,
    fecha_vencimiento ASC";

$result = $conexion->query($sql);

// Contar tareas por estado
$tareas_activas = $conexion->query("SELECT COUNT(*) as count FROM tareas WHERE rol = 'Operador' AND estado = 'Activo'")->fetch_assoc()['count'];
$tareas_pendientes = $conexion->query("SELECT COUNT(*) as count FROM tareas WHERE rol = 'Operador' AND estado = 'Pendiente'")->fetch_assoc()['count'];
$tareas_finalizadas = $conexion->query("SELECT COUNT(*) as count FROM tareas WHERE rol = 'Operador' AND estado = 'Finalizado'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Tareas - Operador</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filter-buttons a, .filter-buttons form button {
            padding: 8px 16px;
            border-radius: 6px;
            border: 1px solid #d0d5dd;
            background: white;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .filter-buttons a:hover, .filter-buttons form button:hover {
            background: #f0f0f0;
        }
        .filter-buttons a.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        .task-card {
            background: white;
            border-left: 4px solid #d0d5dd;
            padding: 16px;
            margin-bottom: 12px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .task-card.activo {
            border-left-color: #10b981;
        }
        .task-card.pendiente {
            border-left-color: #f59e0b;
        }
        .task-card.finalizado {
            border-left-color: #6b7280;
        }
        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 8px;
        }
        .task-title {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            flex: 1;
        }
        .task-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        .badge-activo {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-pendiente {
            background: #fef3c7;
            color: #78350f;
        }
        .badge-finalizado {
            background: #f3f4f6;
            color: #374151;
        }
        .badge-urgente {
            background: #fee2e2;
            color: #7f1d1d;
            margin-left: 8px;
        }
        .badge-alta {
            background: #fecaca;
            color: #991b1b;
            margin-left: 8px;
        }
        .badge-media {
            background: #fed7aa;
            color: #9a3412;
            margin-left: 8px;
        }
        .task-description {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 10px;
            line-height: 1.5;
        }
        .task-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #9ca3af;
            gap: 10px;
            flex-wrap: wrap;
        }
        .task-actions {
            display: flex;
            gap: 8px;
        }
        .btn-state {
            padding: 4px 10px;
            border: 1px solid #d0d5dd;
            background: white;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-state:hover {
            background: #f0f0f0;
        }
        .btn-state.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 24px;
            padding: 0;
            flex-wrap: wrap;
        }
        .stat-box {
            flex: 1;
            min-width: 130px;
            background: white;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary);
        }
        .stat-label {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
            text-transform: uppercase;
        }
        .no-tasks {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
<header class="header">
    <div class="container navbar">
        <a class="brand" href="../../html/operador/index_operador.html">
            <img src="../../img/logo.png">
            <div>
                <div class="title">Den Den Box</div>
                <div class="subtitle">Mis Tareas</div>
            </div>
        </a>
        <nav class="nav-links">
            <a href="../../html/operador/index_operador.html">Inicio</a>
            <a href="../../html/operador/operator_dashboard.php">Dashboard</a>
            <a href="../../programas/auth/logout.php" class="btn-login">Cerrar Sesión</a>
        </nav>
    </div>
</header>

<main class="container" style="padding-top:28px;">
    <h1>Mis Tareas</h1>
    <p>Gestiona tus tareas pendientes y en proceso.</p>

    <!-- Estadísticas -->
    <div class="stats">
        <div class="stat-box">
            <div class="stat-number"><?php echo $tareas_activas; ?></div>
            <div class="stat-label">Activas</div>
        </div>
        <div class="stat-box">
            <div class="stat-number"><?php echo $tareas_pendientes; ?></div>
            <div class="stat-label">Pendientes</div>
        </div>
        <div class="stat-box">
            <div class="stat-number"><?php echo $tareas_finalizadas; ?></div>
            <div class="stat-label">Finalizadas</div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filter-buttons">
        <a href="?estado=todos" class="<?php echo $filtro_estado === 'todos' ? 'active' : ''; ?>">Todas</a>
        <a href="?estado=Activo" class="<?php echo $filtro_estado === 'Activo' ? 'active' : ''; ?>">Activas</a>
        <a href="?estado=Pendiente" class="<?php echo $filtro_estado === 'Pendiente' ? 'active' : ''; ?>">Pendientes</a>
        <a href="?estado=Finalizado" class="<?php echo $filtro_estado === 'Finalizado' ? 'active' : ''; ?>">Finalizadas</a>
    </div>

    <!-- Lista de Tareas -->
    <div>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $estado_clase = strtolower($row['estado']);
                $prioridad_clase = 'badge-' . strtolower($row['prioridad']);
                $id_tarea = $row['id'];
                echo "<div class='task-card {$estado_clase}'>";
                echo "  <div class='task-header'>";
                echo "    <div class='task-title'>" . htmlspecialchars($row['titulo']) . "</div>";
                echo "    <div>";
                echo "      <span class='task-badge badge-{$estado_clase}'>" . ucfirst($row['estado']) . "</span>";
                echo "      <span class='task-badge {$prioridad_clase}'>" . ucfirst($row['prioridad']) . "</span>";
                echo "    </div>";
                echo "  </div>";
                echo "  <div class='task-description'>" . htmlspecialchars($row['descripcion']) . "</div>";
                echo "  <div class='task-footer'>";
                echo "    <span>Creada: " . $row['fecha_creacion'] . "</span>";
                echo "    <span>Vencimiento: " . $row['fecha_vencimiento'] . "</span>";
                echo "    <div class='task-actions'>";
                echo "      <button class='btn-state " . ($row['estado'] === 'Activo' ? 'active' : '') . "' onclick=\"cambiarEstado({$id_tarea}, 'Activo')\">Activo</button>";
                echo "      <button class='btn-state " . ($row['estado'] === 'Pendiente' ? 'active' : '') . "' onclick=\"cambiarEstado({$id_tarea}, 'Pendiente')\">Pendiente</button>";
                echo "      <button class='btn-state " . ($row['estado'] === 'Finalizado' ? 'active' : '') . "' onclick=\"cambiarEstado({$id_tarea}, 'Finalizado')\">Finalizado</button>";
                echo "    </div>";
                echo "  </div>";
                echo "</div>";
            }
        } else {
            echo "<div class='no-tasks'>";
            echo "  <p>No hay tareas en este estado.</p>";
            echo "</div>";
        }
        ?>
    </div>
</main>

<footer class="footer" style="margin-top: 40px;">© 2025 Den Den Box — Proyecto SENA</footer>

<script>
function cambiarEstado(idTarea, nuevoEstado) {
    if (!confirm('¿Deseas cambiar el estado de la tarea a ' + nuevoEstado + '?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('id_tarea', idTarea);
    formData.append('nuevo_estado', nuevoEstado);
    
    fetch('actualizar_estado_tarea.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Tarea actualizada correctamente');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar la tarea');
    });
}
</script>
</body>
</html>
