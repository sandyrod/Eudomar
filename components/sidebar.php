<?php
require_once __DIR__ . '/../conexion.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$userName = 'Usuario';
$userDept = '';
$stmt = $conn->prepare('SELECT name, lastname, department FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($name, $lastname, $department);
if ($stmt->fetch()) {
    $userName = $name . ' ' . $lastname;
    $userDept = $department;
}
$stmt->close();
?>
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="company-brand">
            <div class="brand-icon">
                <i class="fas fa-tint"></i>
            </div>
            <div class="brand-text">
                <h3>Agua de Yaracuy</h3>
                <span>Sistema de Gestión</span>
            </div>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <div class="sidebar-content">
        <div class="user-profile">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-info">
                <h4 id="userName"><?php echo htmlspecialchars($userName); ?></h4>
                <span id="userRole"><?php echo htmlspecialchars($userDept); ?></span>
            </div>
        </div>

        <ul class="nav-menu">
            <li class="nav-item" data-page="dashboard">
                <a href="dashboard.php" class="nav-link" data-section="inicio">
                    <i class="fas fa-home"></i>
                    <span>Inicio</span>
                </a>
            </li>
            
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link submenu-toggle" data-section="pozos">
                    <i class="fas fa-tint"></i>
                    <span>Pozos</span>
                    <i class="fas fa-chevron-right submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" data-section="registros">
                            <i class="far fa-circle"></i>
                            <span>Registros</span>
                        </a>
                    </li>
                    <li class="submenu-item">
                        <a href="motores.php" class="submenu-link" data-section="motores">
                            <i class="far fa-circle"></i>
                            <span>Motores</span>
                        </a>
                    </li>
                    <li class="submenu-item">
                        <a href="bombas.php" class="submenu-link" data-section="bombas">
                            <i class="far fa-circle"></i>
                            <span>Bombas</span>
                        </a>
                    </li>
                    <li class="submenu-item">
                        <a href="pozos.php" class="submenu-link" data-section="estatus">
                            <i class="far fa-circle"></i>
                            <span>Estatus</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link" data-section="cloacas">
                    <i class="fas fa-water"></i>
                    <span>Cloacas</span>
                </a>
            </li>
            
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link submenu-toggle" data-section="reportes">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reportes y Logros</span>
                    <i class="fas fa-chevron-right submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" data-section="historial">
                            <i class="far fa-circle"></i>
                            <span>Historial</span>
                        </a>
                    </li>
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" data-section="crear">
                            <i class="far fa-circle"></i>
                            <span>Crear</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <li class="nav-item" data-page="settings">
                <a href="settings.php" class="nav-link" data-section="configuracion">
                    <i class="fas fa-cog"></i>
                    <span>Configuración</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-footer">
        <button class="logout-btn" id="logoutBtn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar Sesión</span>
        </button>
    </div>
</nav>
