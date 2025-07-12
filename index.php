<?php
// Incluir archivo de conexión reutilizable
require_once 'conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agua de Yaracuy - Sistema de Gestión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/auth.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="auth-container">
        <!-- Animated Background -->
        <div class="water-background">
            <div class="wave wave-1"></div>
            <div class="wave wave-2"></div>
            <div class="wave wave-3"></div>
            <div class="background-overlay"></div>
        </div>
        
        <div class="container-fluid h-100">
            <div class="row h-100 g-0">
                <!-- Left Panel - Branding -->
                <div class="col-lg-6 d-none d-lg-flex branding-section">
                    <div class="branding-content">
                        <div class="company-logo">
                            <div class="logo-icon">
                                <i class="fas fa-tint"></i>
                            </div>
                            <h1 class="company-name">Aguas de Yaracuy</h1>
                            <p class="company-tagline">Sistema de Gestión Operacional</p>
                        </div>
                        
                        <div class="features-showcase">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="feature-text">
                                    <h4>Acceso Rápido</h4>
                                    <p>Información a la mano</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="feature-text">
                                    <h4>Gestión Eficiente</h4>
                                    <p>Optimización de procesos</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="feature-text">
                                    <h4>Colaboración</h4>
                                    <p>Trabajo en equipo efectivo</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Panel - Forms -->
                <div class="col-lg-6 col-12 forms-section">
                    <div class="forms-container">
                        <!-- Mobile Brand -->
                        <div class="mobile-brand d-lg-none">
                            <i class="fas fa-tint"></i>
                            <span>Aguas de Yaracuy</span>
                        </div>
                        
                        <!-- Auth Forms Container - Los formularios se cargarán aquí dinámicamente -->
                        <div id="authFormsContainer">
                            <!-- Loading indicator -->
                            <div class="loading-forms" id="loadingForms">
                                <div class="text-center">
                                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                                    <p>Cargando formularios...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Notifications -->
    <div id="notifications" class="notifications-container"></div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/form-loader.js"></script>
    <script src="assets/js/auth.js"></script>

    <script>
        // Inicializar el cargador de formularios cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', async () => {
            console.log('DOM cargado, inicializando FormLoader...');
            
            try {
                // Crear instancia del cargador de formularios
                window.formLoader = new FormLoader();
                
                // Esperar a que los formularios se carguen
                const checkFormsLoaded = () => {
                    if (window.formLoader && window.formLoader.isReady()) {
                        console.log('Formularios cargados, ocultando loading...');
                        const loadingElement = document.getElementById('loadingForms');
                        if (loadingElement) {
                            loadingElement.style.display = 'none';
                        }
                        
                        // Inicializar la aplicación de autenticación
                        if (window.authApp) {
                            console.log('Reinicializando AuthApp...');
                            window.authApp.init();
                        }
                    } else {
                        setTimeout(checkFormsLoaded, 100);
                    }
                };
                
                checkFormsLoaded();
                
            } catch (error) {
                console.error('Error inicializando FormLoader:', error);
            }
        });
    </script>
</body>
</html>
