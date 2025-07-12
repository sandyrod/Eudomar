<?php
// header.php - Header dinámico con datos del usuario logueado
require_once __DIR__ . '/../conexion.php';
session_start();

// Si no hay usuario logueado, redirigir al login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Obtener datos del usuario
$user_id = $_SESSION['user_id'];
$userData = [
    'user' => '',
    'name' => '',
    'lastname' => '',
    'department' => '',
    'email' => '',
];

$stmt = $conn->prepare('SELECT user, name, lastname, department, email FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($user, $name, $lastname, $department, $email);
if ($stmt->fetch()) {
    $userData = [
        'user' => $user,
        'name' => $name,
        'lastname' => $lastname,
        'department' => $department,
        'email' => $email
    ];
}
$stmt->close();
?>
<header class="main-header">
    <div class="header-left">
        <button class="mobile-menu-toggle" id="mobileMenuToggle" type="button">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title" id="pageTitle">Dashboard</h1>
    </div>
    <div class="header-right">
        <div class="search-box" id="searchBox">
            <i class="fas fa-search"></i>
            <input type="text" id="headerSearch" placeholder="Buscar...">
        </div>
        <div class="header-actions">
            <!-- Notifications Dropdown -->
            <div class="dropdown notifications-dropdown">
                <button class="action-btn dropdown-toggle" id="notificationsBtn" title="Notificaciones" type="button">
                    <i class="fas fa-bell"></i>
                    <span class="badge" id="notificationsBadge">3</span>
                </button>
                <div class="dropdown-menu notifications-menu" id="notificationsMenu">
                    <div class="dropdown-header">
                        <h6>Notificaciones</h6>
                        <span class="badge-count">3 nuevas</span>
                    </div>
                    <div class="notifications-list">
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="notification-content">
                                <h6>Reporte Mensual de Consumo</h6>
                                <p>Generado el 15 de Enero, 2024</p>
                                <small>Hace 2 horas</small>
                            </div>
                            <button class="download-btn" title="Descargar" type="button">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="notification-content">
                                <h6>Análisis de Facturación</h6>
                                <p>Generado el 14 de Enero, 2024</p>
                                <small>Hace 1 día</small>
                            </div>
                            <button class="download-btn" title="Descargar" type="button">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="notification-content">
                                <h6>Reporte de Usuarios Activos</h6>
                                <p>Generado el 13 de Enero, 2024</p>
                                <small>Hace 2 días</small>
                            </div>
                            <button class="download-btn" title="Descargar" type="button">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                    <div class="dropdown-footer">
                        <a href="#" class="view-all-link">Ver todas las notificaciones</a>
                    </div>
                </div>
            </div>

            <!-- User Menu Dropdown -->
            <div class="dropdown user-dropdown">
                <button class="user-menu-toggle dropdown-toggle" id="userMenuToggle" type="button">
                    <div class="user-avatar-small">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="user-name"><?php echo htmlspecialchars($userData['user']); ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu user-menu" id="userMenu">
                    <div class="dropdown-header">
                        <div class="user-info">
                            <div class="user-avatar-large">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="user-details">
                                <h6><?php echo htmlspecialchars($userData['name'] . ' ' . $userData['lastname']); ?></h6>
                                <p><?php echo htmlspecialchars($userData['department']); ?></p>
                                <small><?php echo htmlspecialchars($userData['email']); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item" id="profileMenuItem">
                        <i class="fas fa-user-circle"></i>
                        <span>Mi Perfil</span>
                    </a>
                    <a href="../views/settings.html" class="dropdown-item" id="settingsMenuItem">
                        <i class="fas fa-cog"></i>
                        <span>Configuración</span>
                    </a>
                    <a href="#" class="dropdown-item" id="helpMenuItem">
                        <i class="fas fa-question-circle"></i>
                        <span>Ayuda</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="../logout.php" class="dropdown-item logout-item" id="logoutMenuItem">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
