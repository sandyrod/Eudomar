<?php
// settings.php - Configuraciones del sistema
require_once __DIR__ . '/../conexion.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Variables para mensajes
$message = '';
$messageType = '';

// Procesar formulario de empresa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_empresa') {
    $nombre = $_POST['nombre'] ?? '';
    $rif = $_POST['rif'] ?? '';
    
    if (!empty($nombre) && !empty($rif)) {
        // Verificar si existe un registro en la tabla empresa
        $checkQuery = "SELECT COUNT(*) as count FROM empresa";
        $result = $conn->query($checkQuery);
        $count = $result->fetch_assoc()['count'];
        
        if ($count == 0) {
            // Insertar nuevo registro
            $stmt = $conn->prepare("INSERT INTO empresa (nombre, rif) VALUES (?, ?)");
            $stmt->bind_param("ss", $nombre, $rif);
        } else {
            // Actualizar registro existente con id = 1
            $stmt = $conn->prepare("UPDATE empresa SET nombre = ?, rif = ? WHERE id = 1");
            $stmt->bind_param("ss", $nombre, $rif);
        }
        
        if ($stmt->execute()) {
            $message = "Información de la empresa guardada exitosamente";
            $messageType = "success";
        } else {
            $message = "Error al guardar la información de la empresa";
            $messageType = "error";
        }
        $stmt->close();
    } else {
        $message = "Por favor complete todos los campos";
        $messageType = "error";
    }
}

// Procesar solicitud de respaldo de base de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_backup') {
    // Configuración de la base de datos
    $host = 'localhost';
    $username = 'root'; // Ajustar según tu configuración
    $password = ''; // Ajustar según tu configuración
    $database = 'aguas';
    
    // Nombre del archivo de respaldo
    $backupFile = 'backup_aguas_' . date('Y-m-d_H-i-s') . '.sql';
    $backupPath = sys_get_temp_dir() . '/' . $backupFile;
    
    // Comando mysqldump
    $command = "mysqldump --host={$host} --user={$username}";
    if (!empty($password)) {
        $command .= " --password={$password}";
    }
    $command .= " {$database} > {$backupPath}";
    
    // Ejecutar el comando
    exec($command, $output, $returnVar);
    
    if ($returnVar === 0 && file_exists($backupPath)) {
        // Enviar el archivo para descarga
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $backupFile . '"');
        header('Content-Length: ' . filesize($backupPath));
        readfile($backupPath);
        
        // Eliminar el archivo temporal
        unlink($backupPath);
        exit;
    } else {
        $message = "Error al crear el respaldo de la base de datos";
        $messageType = "error";
    }
}

// Procesar operaciones CRUD de usuarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && strpos($_POST['action'], 'user_') === 0) {
    switch ($_POST['action']) {
        case 'user_create':
            $user = $_POST['user'] ?? '';
            $password = $_POST['password'] ?? '';
            $name = $_POST['name'] ?? '';
            $lastname = $_POST['lastname'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $department = $_POST['department'] ?? '';
            
            if (!empty($user) && !empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (user, password, name, lastname, email, phone, department) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssss", $user, $hashedPassword, $name, $lastname, $email, $phone, $department);
                
                if ($stmt->execute()) {
                    $message = "Usuario creado exitosamente";
                    $messageType = "success";
                } else {
                    $message = "Error al crear el usuario. El nombre de usuario puede estar en uso.";
                    $messageType = "error";
                }
                $stmt->close();
            } else {
                $message = "Usuario y contraseña son obligatorios";
                $messageType = "error";
            }
            break;
            
        case 'user_update':
            $id = $_POST['id'];
            $user = $_POST['user'] ?? '';
            $name = $_POST['name'] ?? '';
            $lastname = $_POST['lastname'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $department = $_POST['department'] ?? '';
            
            if (!empty($user)) {
                if (!empty($_POST['password'])) {
                    // Actualizar con nueva contraseña
                    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET user = ?, password = ?, name = ?, lastname = ?, email = ?, phone = ?, department = ? WHERE id = ?");
                    $stmt->bind_param("sssssssi", $user, $hashedPassword, $name, $lastname, $email, $phone, $department, $id);
                } else {
                    // Actualizar sin cambiar contraseña
                    $stmt = $conn->prepare("UPDATE users SET user = ?, name = ?, lastname = ?, email = ?, phone = ?, department = ? WHERE id = ?");
                    $stmt->bind_param("ssssssi", $user, $name, $lastname, $email, $phone, $department, $id);
                }
                
                if ($stmt->execute()) {
                    $message = "Usuario actualizado exitosamente";
                    $messageType = "success";
                } else {
                    $message = "Error al actualizar el usuario";
                    $messageType = "error";
                }
                $stmt->close();
            } else {
                $message = "El nombre de usuario es obligatorio";
                $messageType = "error";
            }
            break;
            
        case 'user_delete':
            $id = $_POST['id'];
            $currentUserId = $_SESSION['user_id'];
            
            if ($id != $currentUserId) {
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $message = "Usuario eliminado exitosamente";
                    $messageType = "success";
                } else {
                    $message = "Error al eliminar el usuario";
                    $messageType = "error";
                }
                $stmt->close();
            } else {
                $message = "No puedes eliminar tu propio usuario";
                $messageType = "error";
            }
            break;
    }
}

// Obtener todos los usuarios
$users = [];
$result = $conn->query("SELECT id, user, name, lastname, email, phone, department FROM users ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Obtener datos actuales de la empresa
$empresaData = ['nombre' => '', 'rif' => ''];
$result = $conn->query("SELECT nombre, rif FROM empresa WHERE id = 1 LIMIT 1");
if ($result && $result->num_rows > 0) {
    $empresaData = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Agua de Yaracuy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
    <link href="../assets/css/settings.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Container -->
        <div id="sidebarContainer">
            <!-- Loading indicator -->
            <div class="loading-component">
                <i class="fas fa-spinner fa-spin"></i>
                <span>Cargando menú...</span>
            </div>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header Container -->
            <div id="headerContainer">
                <!-- Loading indicator -->
                <div class="loading-component">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Cargando header...</span>
                </div>
            </div>

            <!-- Settings Content -->
            <div class="content-area" id="contentArea">
                <div class="settings-container">
                    <!-- Settings Navigation -->
                    <div class="settings-nav">
                        <div class="nav nav-pills flex-column" id="settings-tab" role="tablist">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="pill" data-bs-target="#general" type="button" role="tab">
                                <i class="fas fa-cog"></i>
                                <span>General</span>
                            </button>
                            <button class="nav-link" id="users-tab" data-bs-toggle="pill" data-bs-target="#users" type="button" role="tab">
                                <i class="fas fa-users"></i>
                                <span>Usuarios</span>
                            </button>
                            <button class="nav-link" id="security-tab" data-bs-toggle="pill" data-bs-target="#security" type="button" role="tab">
                                <i class="fas fa-shield-alt"></i>
                                <span>Seguridad</span>
                            </button>
                            <button class="nav-link" id="notifications-tab" data-bs-toggle="pill" data-bs-target="#notifications" type="button" role="tab">
                                <i class="fas fa-bell"></i>
                                <span>Notificaciones</span>
                            </button>
                            <button class="nav-link" id="system-tab" data-bs-toggle="pill" data-bs-target="#system" type="button" role="tab">
                                <i class="fas fa-server"></i>
                                <span>Sistema</span>
                            </button>
                        </div>
                    </div>

                    <!-- Settings Content -->
                    <div class="settings-content">
                        <div class="tab-content" id="settings-tabContent">
                            <!-- General Settings -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <div class="settings-section">
                                    <div class="section-header">
                                        <h3>Configuración General</h3>
                                        <p>Configuraciones básicas del sistema</p>
                                    </div>

                                    <div class="settings-card">
                                        <h4>Información de la Empresa</h4>
                                        <form class="settings-form" method="POST" id="empresaForm">
                                            <input type="hidden" name="action" value="save_empresa">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Nombre de la Empresa</label>
                                                        <input type="text" class="form-control" name="nombre" id="nombre" value="<?php echo htmlspecialchars($empresaData['nombre']); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>RIF</label>
                                                        <input type="text" class="form-control" name="rif" id="rif" value="<?php echo htmlspecialchars($empresaData['rif']); ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="settings-actions">
                                        <button type="submit" form="empresaForm" class="btn btn-primary">
                                            <i class="fas fa-save"></i>
                                            Guardar Cambios
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                            <i class="fas fa-undo"></i>
                                            Restablecer
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Users Settings -->
                            <div class="tab-pane fade" id="users" role="tabpanel">
                                <div class="settings-section">
                                    <div class="section-header">
                                        <h3>Gestión de Usuarios</h3>
                                        <p>Administrar usuarios y permisos del sistema</p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="clearUserForm()">
                                            <i class="fas fa-plus"></i> Nuevo Usuario
                                        </button>
                                    </div>
                                    
                                    <div class="settings-card">
                                        <h4>Usuarios del Sistema</h4>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Usuario</th>
                                                        <th>Nombre</th>
                                                        <th>Email</th>
                                                        <th>Teléfono</th>
                                                        <th>Departamento</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($users as $user): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                                        <td><?php echo htmlspecialchars($user['user']); ?></td>
                                                        <td><?php echo htmlspecialchars($user['name'] . ' ' . $user['lastname']); ?></td>
                                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                                        <td><?php echo htmlspecialchars($user['department']); ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-warning" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                            <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Settings -->
                            <div class="tab-pane fade" id="security" role="tabpanel">
                                <div class="settings-section">
                                    <div class="section-header">
                                        <h3>Configuración de Seguridad</h3>
                                        <p>Configurar políticas de seguridad y acceso</p>
                                    </div>
                                    <div class="settings-card">
                                        <h4>Políticas de Contraseña</h4>
                                        <p>Configuración de seguridad en desarrollo...</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Notifications Settings -->
                            <div class="tab-pane fade" id="notifications" role="tabpanel">
                                <div class="settings-section">
                                    <div class="section-header">
                                        <h3>Configuración de Notificaciones</h3>
                                        <p>Configurar alertas y notificaciones del sistema</p>
                                    </div>
                                    <div class="settings-card">
                                        <h4>Notificaciones por Email</h4>
                                        <div class="notification-settings">
                                            <div class="notification-item">
                                                <div class="notification-info">
                                                    <h5>Nuevos Clientes</h5>
                                                    <p>Recibir notificación cuando se registre un nuevo cliente</p>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="newCustomers" checked>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="settings-actions">
                                        <button class="btn btn-primary">
                                            <i class="fas fa-save"></i>
                                            Guardar Configuración
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- System Settings -->
                            <div class="tab-pane fade" id="system" role="tabpanel">
                                <div class="settings-section">
                                    <div class="section-header">
                                        <h3>Configuración del Sistema</h3>
                                        <p>Configuraciones técnicas y de mantenimiento</p>
                                    </div>
                                    <div class="settings-card">
                                        <h4>Acciones del Sistema</h4>
                                        <div class="system-actions">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="create_backup">
                                                <button type="submit" class="btn btn-success" onclick="return confirmBackup()">
                                                    <i class="fas fa-download"></i>
                                                    Crear Respaldo Manual
                                                </button>
                                            </form>
                                            <button class="btn btn-warning">
                                                <i class="fas fa-broom"></i>
                                                Limpiar Cache
                                            </button>
                                            <!-- Verificar Actualizaciones button hidden as requested -->
                                            <button class="btn btn-danger">
                                                <i class="fas fa-power-off"></i>
                                                Reiniciar Sistema
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Mobile Overlay Container -->
    <div id="mobileOverlayContainer"></div>

    <!-- Modal para Usuario -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalTitle">Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="userForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="userAction" value="user_create">
                        <input type="hidden" name="id" id="userId">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="user" class="form-label">Nombre de Usuario *</label>
                                    <input type="text" class="form-control" name="user" id="user" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña *</label>
                                    <input type="password" class="form-control" name="password" id="password">
                                    <small class="form-text text-muted" id="passwordHelp">Dejar en blanco para mantener la contraseña actual</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" name="name" id="name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="lastname" class="form-label">Apellido</label>
                                    <input type="text" class="form-control" name="lastname" id="lastname">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" name="phone" id="phone">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="department" class="form-label">Departamento</label>
                            <select class="form-control" name="department" id="department">
                                <option value="">Seleccionar departamento...</option>
                                <option value="administration">Administración</option>
                                <option value="operations">Operaciones</option>
                                <option value="maintenance">Mantenimiento</option>
                                <option value="technical">Técnico</option>
                                <option value="management">Gerencia</option>
                            </select>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="../components/sidebar.js"></script>
    <script src="../components/header.js"></script>
    <script src="../components/dashboard-loader.js"></script>
    <script src="../assets/js/site.js"></script>

    <script>
        // Función global de logout
        function logout() {
            if (typeof window.Swal !== 'undefined') {
                window.Swal.fire({
                    title: '¿Cerrar Sesión?',
                    text: '¿Estás seguro de que deseas cerrar sesión?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#1976d2',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, cerrar sesión',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.Swal.fire({
                            title: 'Cerrando sesión...',
                            text: 'Por favor espera un momento',
                            icon: 'info',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                window.Swal.showLoading();
                            }
                        });

                        localStorage.removeItem('sidebarCollapsed');
                        setTimeout(() => {
                            window.location.href = '../logout.php';
                        }, 1500);
                    }
                });
            } else {
                if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                    window.location.href = '../logout.php';
                }
            }
        }

        // Función para restablecer el formulario
        function resetForm() {
            document.getElementById('nombre').value = '<?php echo htmlspecialchars($empresaData['nombre']); ?>';
            document.getElementById('rif').value = '<?php echo htmlspecialchars($empresaData['rif']); ?>';
        }

        // Función para confirmar la creación del respaldo
        function confirmBackup() {
            Swal.fire({
                title: '¿Crear Respaldo?',
                text: 'Se creará un respaldo completo de la base de datos y se descargará automáticamente.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, crear respaldo',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Creando respaldo...',
                        text: 'Por favor espera mientras se genera el archivo de respaldo',
                        icon: 'info',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    // Submit the form after confirmation
                    event.target.closest('form').submit();
                }
            });
            return false; // Prevent default form submission
        }

        // Funciones para gestión de usuarios
        function clearUserForm() {
            document.getElementById('userForm').reset();
            document.getElementById('userAction').value = 'user_create';
            document.getElementById('userModalTitle').textContent = 'Nuevo Usuario';
            document.getElementById('userId').value = '';
            document.getElementById('password').required = true;
            document.getElementById('passwordHelp').style.display = 'none';
        }

        function editUser(user) {
            document.getElementById('userAction').value = 'user_update';
            document.getElementById('userModalTitle').textContent = 'Editar Usuario';
            document.getElementById('userId').value = user.id;
            document.getElementById('user').value = user.user;
            document.getElementById('password').value = '';
            document.getElementById('password').required = false;
            document.getElementById('name').value = user.name || '';
            document.getElementById('lastname').value = user.lastname || '';
            document.getElementById('email').value = user.email || '';
            document.getElementById('phone').value = user.phone || '';
            document.getElementById('department').value = user.department || '';
            document.getElementById('passwordHelp').style.display = 'block';
            
            new bootstrap.Modal(document.getElementById('userModal')).show();
        }

        function deleteUser(id) {
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
                        <input type="hidden" name="action" value="user_delete">
                        <input type="hidden" name="id" value="${id}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Inicialización de settings
        document.addEventListener('DOMContentLoaded', async () => {
            console.log('Inicializando settings...');
            
            try {
                // Crear y cargar componentes
                window.dashboardLoader = new DashboardLoader();
                const loaded = await window.dashboardLoader.loadAll();
                
                if (loaded) {
                    console.log('Settings inicializado correctamente');
                    
                    // Ocultar indicadores de carga
                    document.querySelectorAll('.loading-component').forEach(el => {
                        el.style.display = 'none';
                    });
                    
                    // Marcar configuración como activa en el sidebar
                    if (window.sidebarComponent) {
                        window.sidebarComponent.setActiveMenuItem('configuracion');
                    }
                } else {
                    console.error('Error inicializando settings');
                }
            } catch (error) {
                console.error('Error en inicialización:', error);
            }
        });

        // Mostrar mensajes de éxito/error
        <?php if ($message): ?>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                icon: '<?php echo $messageType; ?>',
                title: '<?php echo $messageType === "success" ? "Éxito" : "Error"; ?>',
                text: '<?php echo $message; ?>',
                timer: 3000,
                showConfirmButton: false
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>
