<?php
// motores.php - CRUD para motores
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
                $hp = $_POST['hp'] ?? '';
                $voltaje = $_POST['voltaje'] ?? '';
                $tipo = $_POST['tipo'] ?? '';
                $cuerpo = $_POST['cuerpo'] ?? '';
                
                $stmt = $conn->prepare("INSERT INTO motores (hp, voltaje, tipo, cuerpo) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $hp, $voltaje, $tipo, $cuerpo);
                
                if ($stmt->execute()) {
                    $message = "Motor creado exitosamente";
                    $messageType = "success";
                } else {
                    $message = "Error al crear el motor";
                    $messageType = "error";
                }
                $stmt->close();
                break;
                
            case 'update':
                $id = $_POST['id'];
                $hp = $_POST['hp'] ?? '';
                $voltaje = $_POST['voltaje'] ?? '';
                $tipo = $_POST['tipo'] ?? '';
                $cuerpo = $_POST['cuerpo'] ?? '';
                
                $stmt = $conn->prepare("UPDATE motores SET hp = ?, voltaje = ?, tipo = ?, cuerpo = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $hp, $voltaje, $tipo, $cuerpo, $id);
                
                if ($stmt->execute()) {
                    $message = "Motor actualizado exitosamente";
                    $messageType = "success";
                } else {
                    $message = "Error al actualizar el motor";
                    $messageType = "error";
                }
                $stmt->close();
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $stmt = $conn->prepare("DELETE FROM motores WHERE id = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $message = "Motor eliminado exitosamente";
                    $messageType = "success";
                } else {
                    $message = "Error al eliminar el motor";
                    $messageType = "error";
                }
                $stmt->close();
                break;
        }
    }
}

// Obtener todos los motores
$motores = [];
$result = $conn->query("SELECT * FROM motores ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $motores[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motores - Agua de Yaracuy</title>
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
                    <h2><i class="fas fa-cog"></i> Gestión de Motores</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#motorModal" onclick="clearForm()">
                        <i class="fas fa-plus"></i> Nuevo Motor
                    </button>
                </div>

                <!-- Tabla de Motores -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>HP</th>
                                        <th>Voltaje</th>
                                        <th>Tipo</th>
                                        <th>Cuerpo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($motores as $motor): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($motor['id']); ?></td>
                                        <td><?php echo htmlspecialchars($motor['hp']); ?></td>
                                        <td><?php echo htmlspecialchars($motor['voltaje']); ?></td>
                                        <td><?php echo htmlspecialchars($motor['tipo']); ?></td>
                                        <td><?php echo htmlspecialchars($motor['cuerpo']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="editMotor(<?php echo htmlspecialchars(json_encode($motor)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteMotor(<?php echo $motor['id']; ?>)">
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

    <!-- Modal para Motor -->
    <div class="modal fade" id="motorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Motor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="motorForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="action" value="create">
                        <input type="hidden" name="id" id="motorId">
                        
                        <div class="mb-3">
                            <label for="hp" class="form-label">HP</label>
                            <input type="text" class="form-control" name="hp" id="hp" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="voltaje" class="form-label">Voltaje</label>
                            <input type="text" class="form-control" name="voltaje" id="voltaje" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo</label>
                            <input type="text" class="form-control" name="tipo" id="tipo" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cuerpo" class="form-label">Cuerpo</label>
                            <input type="text" class="form-control" name="cuerpo" id="cuerpo" required>
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
            document.getElementById('motorForm').reset();
            document.getElementById('action').value = 'create';
            document.getElementById('modalTitle').textContent = 'Nuevo Motor';
            document.getElementById('motorId').value = '';
        }

        function editMotor(motor) {
            document.getElementById('action').value = 'update';
            document.getElementById('modalTitle').textContent = 'Editar Motor';
            document.getElementById('motorId').value = motor.id;
            document.getElementById('hp').value = motor.hp;
            document.getElementById('voltaje').value = motor.voltaje;
            document.getElementById('tipo').value = motor.tipo;
            document.getElementById('cuerpo').value = motor.cuerpo;
            
            new bootstrap.Modal(document.getElementById('motorModal')).show();
        }

        function deleteMotor(id) {
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
