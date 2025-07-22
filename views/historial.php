<?php
// historial.php - Historial de reportes
require_once __DIR__ . '/../conexion.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Procesar exportación
if (isset($_GET['export'])) {
    $export_type = $_GET['export'];
    $fecha_desde = $_GET['fecha_desde'] ?? '';
    $fecha_hasta = $_GET['fecha_hasta'] ?? '';
    $poso_filter = $_GET['poso_filter'] ?? '';
    
    // Construir query con filtros
    $where_conditions = [];
    $params = [];
    $types = '';
    
    if (!empty($fecha_desde)) {
        $where_conditions[] = "r.fecha >= ?";
        $params[] = $fecha_desde;
        $types .= 's';
    }
    
    if (!empty($fecha_hasta)) {
        $where_conditions[] = "r.fecha <= ?";
        $params[] = $fecha_hasta;
        $types .= 's';
    }
    
    if (!empty($poso_filter)) {
        $where_conditions[] = "r.poso_id = ?";
        $params[] = $poso_filter;
        $types .= 'i';
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    $query = "SELECT r.*, p.nombre as poso_nombre, a.accion, u.user as username, 
                     m.hp as motor_hp, m.voltaje as motor_voltaje, m.tipo as motor_tipo,
                     b.lps as bomba_lps, b.altura as bomba_altura, b.tipo as bomba_tipo
              FROM reportes r
              LEFT JOIN posos p ON r.poso_id = p.id
              LEFT JOIN acciones a ON r.accion_id = a.id
              LEFT JOIN users u ON r.usuario_id = u.id
              LEFT JOIN motores m ON r.motor_id = m.id
              LEFT JOIN bombas b ON r.bomba_id = b.id
              $where_clause
              ORDER BY r.fecha DESC, r.created_at DESC";
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $reportes = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    if ($export_type === 'excel') {
        // Exportar a Excel (CSV)
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=historial_reportes_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // Encabezados
        fputcsv($output, [
            'ID', 'Fecha', 'Pozo', 'Acción', 'Incremento LPS', 'Incremento Usuarios',
            'Motor', 'Bomba', 'Otra Acción', 'Unidad', 'Precio', 'Usuario', 'Fecha Creación'
        ]);
        
        // Datos
        foreach ($reportes as $reporte) {
            $motor_info = '';
            if ($reporte['motor_hp']) {
                $motor_info = $reporte['motor_hp'] . ' HP - ' . $reporte['motor_voltaje'] . 'V - ' . $reporte['motor_tipo'];
            }
            
            $bomba_info = '';
            if ($reporte['bomba_lps']) {
                $bomba_info = $reporte['bomba_lps'] . ' LPS - ' . $reporte['bomba_altura'] . ' - ' . $reporte['bomba_tipo'];
            }
            
            fputcsv($output, [
                $reporte['id'],
                $reporte['fecha'],
                $reporte['poso_nombre'],
                $reporte['accion'],
                $reporte['incremento_lps'] ? 'Sí' : 'No',
                $reporte['incremento_usuarios'] ? 'Sí' : 'No',
                $motor_info,
                $bomba_info,
                $reporte['otra_accion'],
                $reporte['unidad'],
                $reporte['precio'],
                $reporte['username'],
                $reporte['created_at']
            ]);
        }
        
        fclose($output);
        exit;
    } elseif ($export_type === 'pdf') {
        // Exportar a PDF (HTML simple que se puede imprimir como PDF)
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename=historial_reportes_' . date('Y-m-d') . '.html');
        
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Historial de Reportes</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .header { text-align: center; margin-bottom: 20px; }
                .no-break { page-break-inside: avoid; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Historial de Reportes - Agua de Yaracuy</h1>
                <p>Generado el: ' . date('d/m/Y H:i:s') . '</p>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Pozo</th>
                        <th>Acción</th>
                        <th>Inc. LPS</th>
                        <th>Inc. Usuarios</th>
                        <th>Detalles</th>
                        <th>Precio</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($reportes as $reporte) {
            $detalles = '';
            if ($reporte['motor_hp']) {
                $detalles = 'Motor: ' . $reporte['motor_hp'] . ' HP';
            } elseif ($reporte['bomba_lps']) {
                $detalles = 'Bomba: ' . $reporte['bomba_lps'] . ' LPS';
            } elseif ($reporte['otra_accion']) {
                $detalles = $reporte['otra_accion'];
            }
            
            echo '<tr class="no-break">
                    <td>' . date('d/m/Y', strtotime($reporte['fecha'])) . '</td>
                    <td>' . htmlspecialchars($reporte['poso_nombre']) . '</td>
                    <td>' . htmlspecialchars($reporte['accion']) . '</td>
                    <td>' . ($reporte['incremento_lps'] ? 'Sí' : 'No') . '</td>
                    <td>' . ($reporte['incremento_usuarios'] ? 'Sí' : 'No') . '</td>
                    <td>' . htmlspecialchars($detalles) . '</td>
                    <td>$' . number_format($reporte['precio'], 2) . '</td>
                    <td>' . htmlspecialchars($reporte['username']) . '</td>
                  </tr>';
        }
        
        echo '</tbody>
            </table>
        </body>
        </html>';
        exit;
    }
}

// Obtener filtros
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';
$poso_filter = $_GET['poso_filter'] ?? '';

// Construir query con filtros
$where_conditions = [];
$params = [];
$types = '';

if (!empty($fecha_desde)) {
    $where_conditions[] = "r.fecha >= ?";
    $params[] = $fecha_desde;
    $types .= 's';
}

if (!empty($fecha_hasta)) {
    $where_conditions[] = "r.fecha <= ?";
    $params[] = $fecha_hasta;
    $types .= 's';
}

if (!empty($poso_filter)) {
    $where_conditions[] = "r.poso_id = ?";
    $params[] = $poso_filter;
    $types .= 'i';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Obtener reportes con filtros
$query = "SELECT r.*, p.nombre as poso_nombre, a.accion, u.user as username, 
                 m.hp as motor_hp, m.voltaje as motor_voltaje, m.tipo as motor_tipo,
                 b.lps as bomba_lps, b.altura as bomba_altura, b.tipo as bomba_tipo
          FROM reportes r
          LEFT JOIN posos p ON r.poso_id = p.id
          LEFT JOIN acciones a ON r.accion_id = a.id
          LEFT JOIN users u ON r.usuario_id = u.id
          LEFT JOIN motores m ON r.motor_id = m.id
          LEFT JOIN bombas b ON r.bomba_id = b.id
          $where_clause
          ORDER BY r.fecha DESC, r.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$reportes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener pozos para el filtro
$pozos = [];
$result = $conn->query("SELECT id, nombre FROM posos ORDER BY nombre");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pozos[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Reportes - Agua de Yaracuy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Container -->
        <div id="sidebarContainer">
            <div class="loading-component">
                <i class="fas fa-spinner fa-spin"></i>
                <span>Cargando menú...</span>
            </div>
        </div>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header Container -->
            <div id="headerContainer">
                <div class="loading-component">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Cargando header...</span>
                </div>
            </div>
            
            <!-- Content Area -->
            <div class="content-area" id="contentArea">
                <div class="page-header">
                    <h2><i class="fas fa-history"></i> Historial de Reportes</h2>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="fecha_desde" class="form-label">Fecha Desde</label>
                                <input type="date" class="form-control" name="fecha_desde" id="fecha_desde" value="<?php echo htmlspecialchars($fecha_desde); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                                <input type="date" class="form-control" name="fecha_hasta" id="fecha_hasta" value="<?php echo htmlspecialchars($fecha_hasta); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="poso_filter" class="form-label">Pozo</label>
                                <select class="form-control" name="poso_filter" id="poso_filter">
                                    <option value="">Todos los pozos</option>
                                    <?php foreach ($pozos as $pozo): ?>
                                    <option value="<?php echo $pozo['id']; ?>" <?php echo $poso_filter == $pozo['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pozo['nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter"></i> Filtrar
                                </button>
                                <a href="historial.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Botones de Exportación -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Exportar Datos</h5>
                            <div>
                                <a href="?export=excel<?php echo !empty($_SERVER['QUERY_STRING']) ? '&' . str_replace('export=excel&', '', $_SERVER['QUERY_STRING']) : ''; ?>" class="btn btn-success me-2">
                                    <i class="fas fa-file-excel"></i> Exportar Excel
                                </a>
                                <a href="?export=pdf<?php echo !empty($_SERVER['QUERY_STRING']) ? '&' . str_replace('export=pdf&', '', $_SERVER['QUERY_STRING']) : ''; ?>" class="btn btn-danger">
                                    <i class="fas fa-file-pdf"></i> Exportar PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Reportes -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Pozo</th>
                                        <th>Acción</th>
                                        <th>Inc. LPS</th>
                                        <th>Inc. Usuarios</th>
                                        <th>Detalles</th>
                                        <th>Unidad</th>
                                        <th>Precio</th>
                                        <th>Usuario</th>
                                        <th>Creado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($reportes)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                            No se encontraron reportes con los filtros aplicados
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($reportes as $reporte): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($reporte['fecha'])); ?></td>
                                        <td><?php echo htmlspecialchars($reporte['poso_nombre']); ?></td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($reporte['accion']); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($reporte['incremento_lps']): ?>
                                                <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><i class="fas fa-times"></i></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($reporte['incremento_usuarios']): ?>
                                                <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><i class="fas fa-times"></i></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($reporte['motor_hp']): ?>
                                                <small><strong>Motor:</strong> <?php echo $reporte['motor_hp']; ?> HP - <?php echo $reporte['motor_voltaje']; ?>V</small>
                                            <?php elseif ($reporte['bomba_lps']): ?>
                                                <small><strong>Bomba:</strong> <?php echo $reporte['bomba_lps']; ?> LPS - <?php echo $reporte['bomba_altura']; ?></small>
                                            <?php elseif ($reporte['otra_accion']): ?>
                                                <small><?php echo htmlspecialchars($reporte['otra_accion']); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($reporte['unidad']) ?: '-'; ?></td>
                                        <td>
                                            <?php if ($reporte['precio']): ?>
                                                $<?php echo number_format($reporte['precio'], 2); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($reporte['username']); ?></td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y H:i', strtotime($reporte['created_at'])); ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (!empty($reportes)): ?>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Total de reportes encontrados: <strong><?php echo count($reportes); ?></strong>
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../components/sidebar.js"></script>
    <script src="../components/header.js"></script>
    <script src="../components/dashboard-loader.js"></script>
    <script src="../assets/js/dashboard.js"></script>
    
    <script>
        // Initialize dashboard components
        document.addEventListener('DOMContentLoaded', async () => {
            console.log('Inicializando componentes...');
            
            try {
                window.dashboardLoader = new DashboardLoader();
                const loaded = await window.dashboardLoader.loadAll();
                
                if (loaded) {
                    console.log('Componentes inicializados correctamente');
                    
                    // Ocultar indicadores de carga
                    document.querySelectorAll('.loading-component').forEach(el => {
                        el.style.display = 'none';
                    });
                } else {
                    console.error('Error inicializando componentes');
                }
            } catch (error) {
                console.error('Error en inicialización:', error);
            }
        });
    </script>
</body>
</html>
