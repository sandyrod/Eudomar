<?php
// bombas.php - CRUD para bombas
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
                $lps = $_POST['lps'] ?? '';
                $altura = $_POST['altura'] ?? '';
                $tipo = $_POST['tipo'] ?? '';
                $cuerpo = $_POST['cuerpo'] ?? '';
                $diametro = $_POST['diametro'] ?? '';
                
                $stmt = $conn->prepare("INSERT INTO bombas (lps, altura, tipo, cuerpo, diametro) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $lps, $altura, $tipo, $cuerpo, $diametro);
                
                if ($stmt->execute()) {
                    $message = "Bomba creada exitosamente";
                    $messageType = "success";
                } else {
                    $message = "Error al crear la bomba";
                    $messageType = "error";
                }
                $stmt->close();
                break;
                
            case 'update':
                $id = $_POST['id'];
                $lps = $_POST['lps'] ?? '';
                $altura = $_POST['altura'] ?? '';
                $tipo = $_POST['tipo'] ?? '';
                $cuerpo = $_POST['cuerpo'] ?? '';
                $diametro = $_POST['diametro'] ?? '';
                
                $stmt = $conn->prepare("UPDATE bombas SET lps = ?, altura = ?, tipo = ?, cuerpo = ?, diametro = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $lps, $altura, $tipo, $cuerpo, $diametro, $id);
                
                if ($stmt->execute()) {
                    $message = "Bomba actualizada exitosamente";
                    $messageType = "success";
                } else {
                    $message = "Error al actualizar la bomba";
                    $messageType = "error";
                }
                $stmt->close();
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $stmt = $conn->prepare("DELETE FROM bombas WHERE id = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $message = "Bomba eliminada exitosamente";
                    $messageType = "success";
                } else {
                    $message = "Error al eliminar la bomba";
                    $messageType = "error";
                }
                $stmt->close();
                break;
        }
    }
}

// Obtener todas las bombas
$bombas = [];
$result = $conn->query("SELECT * FROM bombas ORDER BY id DESC");
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
    <title>Bombas - Agua de Yaracuy</title>
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
                    <h2><i class="fas fa-pump-soap"></i> Gestión de Bombas</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bombaModal" onclick="clearForm()">
                        <i class="fas fa-plus"></i> Nueva Bomba
                    </button>
                </div>

                <!-- Tabla de Bombas -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>LPS</th>
                                        <th>Altura</th>
                                        <th>Tipo</th>
                                        <th>Cuerpo</th>
                                        <th>Diámetro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bombas as $bomba): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($bomba['id']); ?></td>
                                        <td><?php echo htmlspecialchars($bomba['lps']); ?></td>
                                        <td><?php echo htmlspecialchars($bomba['altura']); ?></td>
                                        <td><?php echo htmlspecialchars($bomba['tipo']); ?></td>
                                        <td><?php echo htmlspecialchars($bomba['cuerpo']); ?></td>
                                        <td><?php echo htmlspecialchars($bomba['diametro']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="editBomba(<?php echo htmlspecialchars(json_encode($bomba)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteBomba(<?php echo $bomba['id']; ?>)">
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

    <!-- Modal para Bomba -->
    <div class="modal fade" id="bombaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nueva Bomba</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="bombaForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="action" value="create">
                        <input type="hidden" name="id" id="bombaId">
                        
                        <div class="mb-3">
                            <label for="lps" class="form-label">LPS (Litros por segundo)</label>
                            <input type="text" class="form-control" name="lps" id="lps" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="altura" class="form-label">Altura</label>
                            <input type="text" class="form-control" name="altura" id="altura" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo</label>
                            <input type="text" class="form-control" name="tipo" id="tipo" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cuerpo" class="form-label">Cuerpo</label>
                            <input type="text" class="form-control" name="cuerpo" id="cuerpo" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="diametro" class="form-label">Diámetro</label>
                            <input type="text" class="form-control" name="diametro" id="diametro" required>
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
            document.getElementById('bombaForm').reset();
            document.getElementById('action').value = 'create';
            document.getElementById('modalTitle').textContent = 'Nueva Bomba';
            document.getElementById('bombaId').value = '';
        }

        function editBomba(bomba) {
            document.getElementById('action').value = 'update';
            document.getElementById('modalTitle').textContent = 'Editar Bomba';
            document.getElementById('bombaId').value = bomba.id;
            document.getElementById('lps').value = bomba.lps;
            document.getElementById('altura').value = bomba.altura;
            document.getElementById('tipo').value = bomba.tipo;
            document.getElementById('cuerpo').value = bomba.cuerpo;
            document.getElementById('diametro').value = bomba.diametro;
            
            new bootstrap.Modal(document.getElementById('bombaModal')).show();
        }

        function deleteBomba(id) {
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
