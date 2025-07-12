<?php
// dashboard.php - Dashboard principal (requiere conexión a la base de datos)
require_once __DIR__ . '/../conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Agua de Yaracuy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            
            <!-- Dashboard Content -->
            <div class="content-area" id="contentArea">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card primary">
                        <div class="stat-icon">
                            <i class="fas fa-tint"></i>
                        </div>
                        <div class="stat-content">
                            <h3>12</h3>
                            <p>Pozos Operativos</p>
                        </div>
                    </div>
                    <div class="stat-card danger">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-content">
                            <h3>100</h3>
                            <p>Pozos Fuera de Servicio</p>
                        </div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="stat-content">
                            <h3>4</h3>
                            <p>Cloacas Rehabilitadas</p>
                        </div>
                    </div>
                </div>

                <!-- Monthly Chart -->
                <div class="monthly-chart-section">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h4>Últimos 4 Meses</h4>
                            <div class="chart-controls">
                                <select>
                                    <option>2024</option>
                                    <option>2025</option>
                                </select>
                            </div>
                        </div>
                        <div class="chart-body">
                            <div class="horizontal-bars">
                                <div class="bar-item">
                                    <div class="bar-label">Enero</div>
                                    <div class="bar-container">
                                        <div class="bar bar-blue" style="width: 5.4%;" data-value="180,000"></div>
                                    </div>
                                    <div class="bar-value">180,000</div>
                                </div>
                                <div class="bar-item">
                                    <div class="bar-label">Febrero</div>
                                    <div class="bar-container">
                                        <div class="bar bar-red" style="width: 100%;" data-value="3,345,000"></div>
                                    </div>
                                    <div class="bar-value">3,345,000</div>
                                </div>
                                <div class="bar-item">
                                    <div class="bar-label">Marzo</div>
                                    <div class="bar-container">
                                        <div class="bar bar-green" style="width: 25.1%;" data-value="840,000"></div>
                                    </div>
                                    <div class="bar-value">840,000</div>
                                </div>
                                <div class="bar-item">
                                    <div class="bar-label">Abril</div>
                                    <div class="bar-container">
                                        <div class="bar bar-yellow" style="width: 7.2%;" data-value="240,000"></div>
                                    </div>
                                    <div class="bar-value">240,000</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h4>Acciones Rápidas</h4>
                    <div class="actions-grid">
                        <button class="action-card primary">
                            <div class="action-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="action-content">
                                <h5>Generar Reporte</h5>
                                <p>Crear Nuevo Reporte</p>
                            </div>
                        </button>
                        <button class="action-card success">
                            <div class="action-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="action-content">
                                <h5>Reportes de Pozo</h5>
                                <p>Ver analisis Detallado</p>
                            </div>
                        </button>
                        <button class="action-card warning">
                            <div class="action-icon">
                                <i class="fas fa-wrench"></i>
                            </div>
                            <div class="action-content">
                                <h5>Reporte de Cloacas</h5>
                                <p>Ver analisis Detallado</p>
                            </div>
                        </button>
                        <button class="action-card info">
                            <div class="action-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="action-content">
                                <h5>Ver logros</h5>
                                <p>Consultar Progreso</p>
                            </div>
                        </button>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="recent-activity">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="activity-card">
                                <div class="activity-header">
                                    <h4>Actividad Reciente</h4>
                                    <a href="#" class="view-all">Ver todo</a>
                                </div>
                                <div class="activity-list">
                                    <div class="activity-item">
                                        <div class="activity-icon success">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p><strong>Pago procesado</strong> - Cliente #12847</p>
                                            <span class="activity-time">Hace 5 minutos</span>
                                        </div>
                                    </div>
                                    <div class="activity-item">
                                        <div class="activity-icon warning">
                                            <i class="fas fa-exclamation"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p><strong>Reporte de fuga</strong> - Sector Norte</p>
                                            <span class="activity-time">Hace 15 minutos</span>
                                        </div>
                                    </div>
                                    <div class="activity-item">
                                        <div class="activity-icon primary">
                                            <i class="fas fa-user-plus"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p><strong>Nuevo cliente</strong> - María González</p>
                                            <span class="activity-time">Hace 1 hora</span>
                                        </div>
                                    </div>
                                    <div class="activity-item">
                                        <div class="activity-icon info">
                                            <i class="fas fa-tools"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p><strong>Mantenimiento completado</strong> - Bomba #3</p>
                                            <span class="activity-time">Hace 2 horas</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="activity-card">
                                <div class="activity-header">
                                    <h4>Alertas del Sistema</h4>
                                    <a href="#" class="view-all">Ver todo</a>
                                </div>
                                <div class="alerts-list">
                                    <div class="alert-item high">
                                        <div class="alert-indicator"></div>
                                        <div class="alert-content">
                                            <h5>Presión baja en Sector Este</h5>
                                            <p>Se requiere revisión inmediata</p>
                                            <span class="alert-time">Hace 10 minutos</span>
                                        </div>
                                    </div>
                                    <div class="alert-item medium">
                                        <div class="alert-indicator"></div>
                                        <div class="alert-content">
                                            <h5>Mantenimiento programado</h5>
                                            <p>Bomba principal - Mañana 8:00 AM</p>
                                            <span class="alert-time">Programado</span>
                                        </div>
                                    </div>
                                    <div class="alert-item low">
                                        <div class="alert-indicator"></div>
                                        <div class="alert-content">
                                            <h5>Actualización disponible</h5>
                                            <p>Nueva versión del sistema</p>
                                            <span class="alert-time">Disponible</span>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
                        }, 500);
                    }
                });
            } else {
                if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                    window.location.href = '../logout.php';
                }
            }
        }

        // Inicialización del dashboard
        document.addEventListener('DOMContentLoaded', async () => {
            console.log('Inicializando dashboard...');
            
            try {
                // Crear y cargar componentes
                window.dashboardLoader = new DashboardLoader();
                const loaded = await window.dashboardLoader.loadAll();
                
                if (loaded) {
                    console.log('Dashboard inicializado correctamente');
                    
                    // Ocultar indicadores de carga
                    document.querySelectorAll('.loading-component').forEach(el => {
                        el.style.display = 'none';
                    });
                    
                    // Animar gráfico de barras
                    setTimeout(() => {
                        animateChartBars();
                    }, 500);
                } else {
                    console.error('Error inicializando dashboard');
                }
            } catch (error) {
                console.error('Error en inicialización:', error);
            }
        });

        // Función para animar las barras del gráfico
        function animateChartBars() {
            const bars = document.querySelectorAll('.bar');
            
            bars.forEach((bar, index) => {
                const originalStyle = bar.getAttribute('style');
                const targetWidth = originalStyle.match(/width:\s*([^;]+)/)?.[1] || '0%';
                
                bar.style.width = '0%';
                
                setTimeout(() => {
                    bar.style.width = targetWidth;
                }, index * 300);
            });
        }
    </script>
</body>
</html>
