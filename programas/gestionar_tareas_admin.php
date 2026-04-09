<?php
session_start();
include 'conexion.php';

// Verificar que es admin (rol 1)
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../html/login.html");
    exit();
}

// Obtener filtro de estado (si existe)
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : 'todos';
$filtro_rol = isset($_GET['rol']) ? $_GET['rol'] : 'todos';

// Construir la consulta SQL
$sql = "SELECT id, titulo, descripcion, rol, estado, fecha_creacion, fecha_vencimiento, prioridad FROM tareas WHERE 1=1";

if ($filtro_rol !== 'todos') {
    $sql .= " AND rol = '" . $conn->real_escape_string($filtro_rol) . "'";
}

if ($filtro_estado !== 'todos') {
    $sql .= " AND estado = '" . $conn->real_escape_string($filtro_estado) . "'";
}

$sql .= " ORDER BY 
    CASE 
        WHEN prioridad = 'Urgente' THEN 1
        WHEN prioridad = 'Alta' THEN 2
        WHEN prioridad = 'Media' THEN 3
        ELSE 4
    END,
    fecha_vencimiento ASC";

$result = $conn->query($sql);

// Contar tareas por estado
$tareas_activas = $conn->query("SELECT COUNT(*) as count FROM tareas WHERE estado = 'Activo'")->fetch_assoc()['count'];
$tareas_pendientes = $conn->query("SELECT COUNT(*) as count FROM tareas WHERE estado = 'Pendiente'")->fetch_assoc()['count'];
$tareas_finalizadas = $conn->query("SELECT COUNT(*) as count FROM tareas WHERE estado = 'Finalizado'")->fetch_assoc()['count'];

// Obtener roles únicos disponibles
$roles_result = $conn->query("SELECT DISTINCT rol FROM tareas ORDER BY rol");
$roles = [];
while ($role = $roles_result->fetch_assoc()) {
    $roles[] = $role['rol'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tareas - Administrador</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 16px;
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
        .filter-row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 12px;
        }
        .filter-group {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .filter-group label {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }
        .filter-group select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #d0d5dd;
            background: white;
            font-size: 14px;
            cursor: pointer;
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
            gap: 16px;
        }
        .task-title {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            flex: 1;
        }
        .task-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
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
        }
        .badge-alta {
            background: #fecaca;
            color: #991b1b;
        }
        .badge-media {
            background: #fed7aa;
            color: #9a3412;
        }
        .badge-rol {
            background: #dbeafe;
            color: #1e40af;
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
            flex-wrap: wrap;
            gap: 10px;
        }
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 24px;
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
        <a class="brand" href="../index_admin.html">
            <img src="../img/logo.png">
            <div>
                <div class="title">Den Den Box</div>
                <div class="subtitle">Gestión de Tareas</div>
            </div>
        </a>
        <nav class="nav-links">
            <a href="../index_admin.html">Inicio</a>
            <a href="../admin_dashboard.html">Dashboard</a>
            <a href="logout.php" class="btn-login">Cerrar Sesión</a>
        </nav>
    </div>
</header>

<main class="container" style="padding-top:28px;">
    <h1>Gestión de Tareas del Equipo</h1>
    <p>Supervisa y gestiona las tareas asignadas a los diferentes roles.</p>

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
    <div class="filter-section">
        <h3 style="margin-top: 0;">Filtros</h3>
        <div class="filter-row">
            <div class="filter-group">
                <label>Por Estado:</label>
                <div class="filter-buttons">
                    <a href="?estado=todos&rol=<?php echo $filtro_rol; ?>" class="<?php echo $filtro_estado === 'todos' ? 'active' : ''; ?>">Todas</a>
                    <a href="?estado=Activo&rol=<?php echo $filtro_rol; ?>" class="<?php echo $filtro_estado === 'Activo' ? 'active' : ''; ?>">Activas</a>
                    <a href="?estado=Pendiente&rol=<?php echo $filtro_rol; ?>" class="<?php echo $filtro_estado === 'Pendiente' ? 'active' : ''; ?>">Pendientes</a>
                    <a href="?estado=Finalizado&rol=<?php echo $filtro_rol; ?>" class="<?php echo $filtro_estado === 'Finalizado' ? 'active' : ''; ?>">Finalizadas</a>
                </div>
            </div>
        </div>
        <div class="filter-row">
            <div class="filter-group">
                <label>Por Rol:</label>
                <select onchange="location.href='?estado=<?php echo $filtro_estado; ?>&rol=' + this.value;">
                    <option value="todos">Todas los roles</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo htmlspecialchars($role); ?>" <?php echo $filtro_rol === $role ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($role); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Lista de Tareas -->
    <div>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $estado_clase = strtolower($row['estado']);
                $prioridad_clase = 'badge-' . strtolower($row['prioridad']);
                echo "<div class='task-card {$estado_clase}'>";
                echo "  <div class='task-header'>";
                echo "    <div>";
                echo "      <div class='task-title'>" . htmlspecialchars($row['titulo']) . "</div>";
                echo "      <div style='font-size: 12px; color: #9ca3af; margin-top: 4px;'>Rol: " . htmlspecialchars($row['rol']) . "</div>";
                echo "    </div>";
                echo "    <div class='task-badges'>";
                echo "      <span class='task-badge badge-{$estado_clase}'>" . ucfirst($row['estado']) . "</span>";
                echo "      <span class='task-badge {$prioridad_clase}'>" . ucfirst($row['prioridad']) . "</span>";
                echo "    </div>";
                echo "  </div>";
                echo "  <div class='task-description'>" . htmlspecialchars($row['descripcion']) . "</div>";
                echo "  <div class='task-footer'>";
                echo "    <span>Creada: " . $row['fecha_creacion'] . "</span>";
                echo "    <span>Vencimiento: " . $row['fecha_vencimiento'] . "</span>";
                echo "  </div>";
                echo "</div>";
            }
        } else {
            echo "<div class='no-tasks'>";
            echo "  <p>No hay tareas con los filtros seleccionados.</p>";
            echo "</div>";
        }
        ?>
    </div>
</main>

<footer class="footer" style="margin-top: 40px;">© 2025 Den Den Box — Proyecto SENA</footer>
</body>
</html>

<?php
$conn->close();
?>
