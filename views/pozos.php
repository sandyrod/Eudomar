<?php
// pozos.php - CRUD para pozos (estatus)
require_once __DIR__ . '/../conexion.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Manejar operaciones CRUD
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $nombre = $_POST['nombre'] ?? '';
                $municipio = $_POST['municipio'] ?? '';
                $norte = $_POST['norte'] ?? '';
                $este = $_POST['este'] ?? '';
                $sectores = $_POST['sectores'] ?? '';
                $habitantes = $_POST['habitantes'] ?? '';
                $lps = $_POST['lps'] ?? '';
                $statuso = $_POST['statuso'] ?? '';
                $statusi = $_POST['statusi'] ?? '';
                $motor_id = !empty($_POST['motor_id']) ? $_POST['motor_id'] : null;
                $bomba_id = !empty($_POST['bomba_id']) ? $_POST['bomba_id'] : null;
                
                $stmt = $conn->prepare("INSERT INTO posos (nombre, municipio, norte, este, sectores, habitantes, lps, statuso, statusi, motor_id, bomba_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssssii", $nombre, $municipio, $norte, $este, $sectores, $habitantes, $lps, $statuso, $statusi, $motor_id, $bomba_id);
                
                if ($stmt->execute()) {
                    $message = "Pozo creado exitosamente";
                    $messageType = "success";
                } else {
                    $message = "Error al crear el pozo";
                    $messageType = "error";
                }
                $stmt->close();
                break;
                
            case 'update':
                $id = $_POST['id'];
                $nombre = $_POST['nombre'] ?? '';
                $municipio = $_POST['municipio'] ?? '';
                $norte = $_POST['norte'] ?? '';
                $este = $_POST['este'] ?? '';
                $sectores = $_POST['sectores'] ?? '';
                $habitantes = $_POST['habitantes'] ?? '';
                $lps = $_POST['lps'] ?? '';
                $statuso = $_POST['statuso'] ?? '';
                $statusi = $_POST['statusi'] ?? '';
                $motor_id = !empty($_POST['motor_id']) ? $_POST['motor_id'] : null;
                $bomba_id = !empty($_POST['bomba_id']) ? $_POST['bomba_id'] : null;
                
                $stmt = $conn->prepare("UPDATE posos SET nombre = ?, municipio = ?, norte = ?, este = ?, sectores = ?, habitantes = ?, lps = ?, statuso = ?, statusi = ?, motor_id = ?, bomba_id = ? WHERE id = ?");
                $stmt->bind_param("sssssssssiii", $nombre, $municipio, $norte, $este, $sectores, $habitantes, $lps, $statuso, $statusi, $motor_id, $bomba_id, $id);
                
                if ($stmt->execute()) {
                    $message = "Pozo actualizado exitosamente";
                    $messageType = "success";
                } else {
                    $message = "Error al actualizar el pozo";
                    $messageType = "error";
                }
                $stmt->close();
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $stmt = $conn->prepare("DELETE FROM posos WHERE id = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $message = "Pozo eliminado exitosamente";
                    $messageType = "success";
                } else {
                    $message = "Error al eliminar el pozo";
                    $messageType = "error";
                }
                $stmt->close();
                break;
        }
    }
}

// Obtener todos los pozos con información de motores, bombas y municipios
$pozos = [];
$query = "SELECT p.*, m.hp as motor_hp, m.voltaje as motor_voltaje, b.lps as bomba_lps, b.tipo as bomba_tipo, mu.municipio as municipio_nombre
          FROM posos p 
          LEFT JOIN motores m ON p.motor_id = m.id 
          LEFT JOIN bombas b ON p.bomba_id = b.id 
          LEFT JOIN municipios mu ON p.municipio = mu.id
          ORDER BY p.id DESC";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pozos[] = $row;
    }
}

// Obtener motores para los selects
$motores = [];
$result = $conn->query("SELECT id, hp, voltaje, tipo FROM motores ORDER BY hp");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $motores[] = $row;
    }
}

// Obtener bombas para los selects
$bombas = [];
$result = $conn->query("SELECT id, lps, altura, tipo FROM bombas ORDER BY lps");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $bombas[] = $row;
    }
}

// Obtener municipios para los selects
$municipios = [];
$result = $conn->query("SELECT id, municipio FROM municipios ORDER BY municipio");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $municipios[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pozos - Agua de Yaracuy</title>
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
                    <h2><i class="fas fa-tint"></i> Gestión de Pozos</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pozoModal" onclick="clearForm()">
                        <i class="fas fa-plus"></i> Nuevo Pozo
                    </button>
                </div>

                <!-- Tabla de Pozos -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Municipio</th>
                                        <th>Coordenadas</th>
                                        <th>Habitantes</th>
                                        <th>LPS</th>
                                        <th>Status Operativo</th>
                                        <th>Status Instalación</th>
                                        <th>Motor</th>
                                        <th>Bomba</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pozos as $pozo): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($pozo['id']); ?></td>
                                        <td><?php echo htmlspecialchars($pozo['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($pozo['municipio_nombre'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($pozo['norte'] . ', ' . $pozo['este']); ?></td>
                                        <td><?php echo htmlspecialchars($pozo['habitantes']); ?></td>
                                        <td><?php echo htmlspecialchars($pozo['lps']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $pozo['statuso'] === 'Operativo' ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo htmlspecialchars($pozo['statuso']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $pozo['statusi'] === 'Instalado' ? 'bg-success' : 'bg-warning'; ?>">
                                                <?php echo htmlspecialchars($pozo['statusi']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $pozo['motor_hp'] ? htmlspecialchars($pozo['motor_hp'] . ' HP') : 'N/A'; ?></td>
                                        <td><?php echo $pozo['bomba_lps'] ? htmlspecialchars($pozo['bomba_lps'] . ' LPS') : 'N/A'; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="editPozo(<?php echo htmlspecialchars(json_encode($pozo)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deletePozo(<?php echo $pozo['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal para Pozo -->
    <div class="modal fade" id="pozoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Pozo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="pozoForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="action" value="create">
                        <input type="hidden" name="id" id="pozoId">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" name="nombre" id="nombre" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="municipio" class="form-label">Municipio</label>
                                    <select class="form-control" name="municipio" id="municipio" required>
                                        <option value="">Seleccionar municipio...</option>
                                        <?php foreach ($municipios as $municipio): ?>
                                        <option value="<?php echo $municipio['id']; ?>">
                                            <?php echo htmlspecialchars($municipio['municipio']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="norte" class="form-label">Coordenada Norte</label>
                                    <input type="text" class="form-control" name="norte" id="norte">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="este" class="form-label">Coordenada Este</label>
                                    <input type="text" class="form-control" name="este" id="este">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="sectores" class="form-label">Sectores</label>
                            <textarea class="form-control" name="sectores" id="sectores" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="habitantes" class="form-label">Habitantes</label>
                                    <input type="text" class="form-control" name="habitantes" id="habitantes">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="lps" class="form-label">LPS</label>
                                    <input type="text" class="form-control" name="lps" id="lps">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="statuso" class="form-label">Status Operativo</label>
                                    <select class="form-control" name="statuso" id="statuso">
                                        <option value="">Seleccionar...</option>
                                        <option value="Operativo">Operativo</option>
                                        <option value="No Operativo">No Operativo</option>
                                        <option value="En Mantenimiento">En Mantenimiento</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="statusi" class="form-label">Status Instalación</label>
                                    <select class="form-control" name="statusi" id="statusi">
                                        <option value="">Seleccionar...</option>
                                        <option value="Instalado">Instalado</option>
                                        <option value="Por Instalar">Por Instalar</option>
                                        <option value="En Proceso">En Proceso</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="motor_id" class="form-label">Motor</label>
                                    <select class="form-control" name="motor_id" id="motor_id">
                                        <option value="">Sin motor asignado</option>
                                        <?php foreach ($motores as $motor): ?>
                                        <option value="<?php echo $motor['id']; ?>">
                                            <?php echo htmlspecialchars($motor['hp'] . ' HP - ' . $motor['voltaje'] . 'V - ' . $motor['tipo']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bomba_id" class="form-label">Bomba</label>
                                    <select class="form-control" name="bomba_id" id="bomba_id">
                                        <option value="">Sin bomba asignada</option>
                                        <?php foreach ($bombas as $bomba): ?>
                                        <option value="<?php echo $bomba['id']; ?>">
                                            <?php echo htmlspecialchars($bomba['lps'] . ' LPS - ' . $bomba['altura'] . ' - ' . $bomba['tipo']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
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

        function clearForm() {
            document.getElementById('pozoForm').reset();
            document.getElementById('action').value = 'create';
            document.getElementById('modalTitle').textContent = 'Nuevo Pozo';
            document.getElementById('pozoId').value = '';
        }

        function editPozo(pozo) {
            document.getElementById('action').value = 'update';
            document.getElementById('modalTitle').textContent = 'Editar Pozo';
            document.getElementById('pozoId').value = pozo.id;
            document.getElementById('nombre').value = pozo.nombre;
            document.getElementById('municipio').value = pozo.municipio;
            document.getElementById('norte').value = pozo.norte;
            document.getElementById('este').value = pozo.este;
            document.getElementById('sectores').value = pozo.sectores;
            document.getElementById('habitantes').value = pozo.habitantes;
            document.getElementById('lps').value = pozo.lps;
            document.getElementById('statuso').value = pozo.statuso;
            document.getElementById('statusi').value = pozo.statusi;
            document.getElementById('motor_id').value = pozo.motor_id || '';
            document.getElementById('bomba_id').value = pozo.bomba_id || '';
            
            new bootstrap.Modal(document.getElementById('pozoModal')).show();
        }

        function deletePozo(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="${id}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</body>
</html>
