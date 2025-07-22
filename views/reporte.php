<?php
// reporte.php - Sistema de reportes
require_once __DIR__ . '/../conexion.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Variables para mensajes
$message = '';
$messageType = '';

// Procesar formulario de reporte
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_report') {
    $poso_id = $_POST['poso_id'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $incremento_lps = isset($_POST['incremento_lps']) ? 1 : 0;
    $incremento_usuarios = isset($_POST['incremento_usuarios']) ? 1 : 0;
    $accion_id = $_POST['accion_id'] ?? '';
    $motor_id = !empty($_POST['motor_id']) ? $_POST['motor_id'] : null;
    $bomba_id = !empty($_POST['bomba_id']) ? $_POST['bomba_id'] : null;
    $otra_accion = $_POST['otra_accion'] ?? '';
    $unidad = $_POST['unidad'] ?? '';
    $precio = $_POST['precio'] ?? '';
    
    if (!empty($poso_id) && !empty($fecha) && !empty($accion_id)) {
        // Obtener la acción seleccionada
        $stmt = $conn->prepare("SELECT accion FROM acciones WHERE id = ?");
        $stmt->bind_param("i", $accion_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $accion_data = $result->fetch_assoc();
        $stmt->close();
        
        if ($accion_data) {
            $accion_nombre = $accion_data['accion'];
            
            // Insertar el reporte
            $stmt = $conn->prepare("INSERT INTO reportes (poso_id, fecha, incremento_lps, incremento_usuarios, accion_id, motor_id, bomba_id, otra_accion, unidad, precio, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isiiiisssdi", $poso_id, $fecha, $incremento_lps, $incremento_usuarios, $accion_id, $motor_id, $bomba_id, $otra_accion, $unidad, $precio, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                // Actualizar el pozo si es cambio de motor o bomba
                if ($accion_nombre === 'Cambio de Motor' && !empty($motor_id)) {
                    $update_stmt = $conn->prepare("UPDATE posos SET motor_id = ? WHERE id = ?");
                    $update_stmt->bind_param("ii", $motor_id, $poso_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                } elseif ($accion_nombre === 'Cambio de Bomba' && !empty($bomba_id)) {
                    $update_stmt = $conn->prepare("UPDATE posos SET bomba_id = ? WHERE id = ?");
                    $update_stmt->bind_param("ii", $bomba_id, $poso_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
                
                $message = "Reporte creado exitosamente";
                $messageType = "success";
            } else {
                $message = "Error al crear el reporte";
                $messageType = "error";
            }
            $stmt->close();
        } else {
            $message = "Acción no válida";
            $messageType = "error";
        }
    } else {
        $message = "Por favor complete todos los campos obligatorios";
        $messageType = "error";
    }
}

// Obtener pozos para el select
$pozos = [];
$result = $conn->query("SELECT id, nombre FROM posos ORDER BY nombre");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pozos[] = $row;
    }
}

// Obtener acciones para el select
$acciones = [];
$result = $conn->query("SELECT id, accion FROM acciones ORDER BY accion");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $acciones[] = $row;
    }
}

// Obtener motores para el select
$motores = [];
$result = $conn->query("SELECT id, hp, voltaje, tipo FROM motores ORDER BY hp");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $motores[] = $row;
    }
}

// Obtener bombas para el select
$bombas = [];
$result = $conn->query("SELECT id, lps, altura, tipo FROM bombas ORDER BY lps");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $bombas[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Reporte - Agua de Yaracuy</title>
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
                    <h2><i class="fas fa-file-alt"></i> Crear Reporte</h2>
                </div>

                <!-- Formulario de Reporte -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" id="reporteForm">
                            <input type="hidden" name="action" value="create_report">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="poso_id" class="form-label">Pozo *</label>
                                        <select class="form-control" name="poso_id" id="poso_id" required>
                                            <option value="">Seleccionar pozo...</option>
                                            <?php foreach ($pozos as $pozo): ?>
                                            <option value="<?php echo $pozo['id']; ?>">
                                                <?php echo htmlspecialchars($pozo['nombre']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fecha" class="form-label">Fecha del Reporte *</label>
                                        <input type="date" class="form-control" name="fecha" id="fecha" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="incremento_lps" id="incremento_lps">
                                            <label class="form-check-label" for="incremento_lps">
                                                ¿Se incrementaron los LPS?
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="incremento_usuarios" id="incremento_usuarios">
                                            <label class="form-check-label" for="incremento_usuarios">
                                                ¿Se incrementó el número de usuarios?
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="accion_id" class="form-label">Acción Ejecutada *</label>
                                <select class="form-control" name="accion_id" id="accion_id" required onchange="handleAccionChange()">
                                    <option value="">Seleccionar acción...</option>
                                    <?php foreach ($acciones as $accion): ?>
                                    <option value="<?php echo $accion['id']; ?>" data-accion="<?php echo htmlspecialchars($accion['accion']); ?>">
                                        <?php echo htmlspecialchars($accion['accion']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Campo para Cambio de Motor -->
                            <div class="mb-3" id="motor_field" style="display: none;">
                                <label for="motor_id" class="form-label">Seleccionar Motor</label>
                                <select class="form-control" name="motor_id" id="motor_id">
                                    <option value="">Seleccionar motor...</option>
                                    <?php foreach ($motores as $motor): ?>
                                    <option value="<?php echo $motor['id']; ?>">
                                        <?php echo htmlspecialchars($motor['hp'] . ' HP - ' . $motor['voltaje'] . 'V - ' . $motor['tipo']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Campo para Cambio de Bomba -->
                            <div class="mb-3" id="bomba_field" style="display: none;">
                                <label for="bomba_id" class="form-label">Seleccionar Bomba</label>
                                <select class="form-control" name="bomba_id" id="bomba_id">
                                    <option value="">Seleccionar bomba...</option>
                                    <?php foreach ($bombas as $bomba): ?>
                                    <option value="<?php echo $bomba['id']; ?>">
                                        <?php echo htmlspecialchars($bomba['lps'] . ' LPS - ' . $bomba['altura'] . ' - ' . $bomba['tipo']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Campo para Otras Acciones -->
                            <div class="mb-3" id="otra_accion_field" style="display: none;">
                                <label for="otra_accion" class="form-label">Descripción de la Acción</label>
                                <textarea class="form-control" name="otra_accion" id="otra_accion" rows="3" placeholder="Describa la acción ejecutada..."></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="unidad" class="form-label">Unidad</label>
                                        <input type="text" class="form-control" name="unidad" id="unidad" placeholder="Ej: Piezas, Metros, Horas...">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="precio" class="form-label">Precio</label>
                                        <input type="number" step="0.01" class="form-control" name="precio" id="precio" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary me-2" onclick="resetForm()">
                                    <i class="fas fa-undo"></i> Limpiar
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Crear Reporte
                                </button>
                            </div>
                        </form>
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
            
            // Establecer fecha actual por defecto
            document.getElementById('fecha').value = new Date().toISOString().split('T')[0];
        });

        // Mostrar mensajes
        <?php if ($message): ?>
        Swal.fire({
            icon: '<?php echo $messageType; ?>',
            title: '<?php echo $messageType === "success" ? "Éxito" : "Error"; ?>',
            text: '<?php echo $message; ?>',
            timer: 3000,
            showConfirmButton: false
        });
        <?php endif; ?>

        // Manejar cambio de acción
        function handleAccionChange() {
            const accionSelect = document.getElementById('accion_id');
            const selectedOption = accionSelect.options[accionSelect.selectedIndex];
            const accionText = selectedOption.getAttribute('data-accion');
            
            // Ocultar todos los campos dinámicos
            document.getElementById('motor_field').style.display = 'none';
            document.getElementById('bomba_field').style.display = 'none';
            document.getElementById('otra_accion_field').style.display = 'none';
            
            // Limpiar valores
            document.getElementById('motor_id').value = '';
            document.getElementById('bomba_id').value = '';
            document.getElementById('otra_accion').value = '';
            
            // Mostrar campo apropiado según la acción
            if (accionText === 'Cambio de Motor') {
                document.getElementById('motor_field').style.display = 'block';
            } else if (accionText === 'Cambio de Bomba') {
                document.getElementById('bomba_field').style.display = 'block';
            } else if (accionText === 'Otras acciones') {
                document.getElementById('otra_accion_field').style.display = 'block';
            }
        }

        // Limpiar formulario
        function resetForm() {
            document.getElementById('reporteForm').reset();
            document.getElementById('fecha').value = new Date().toISOString().split('T')[0];
            handleAccionChange();
        }
    </script>
</body>
</html>
