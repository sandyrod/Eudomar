import { Chart } from "@/components/ui/chart"
console.log("Dashboard.js se está ejecutando...")
// Configuración global del dashboard
const DashboardConfig = {
  charts: {
    consumption: null,
    sectors: null,
  },
  sidebar: {
    isCollapsed: false,
    isMobileOpen: false,
  },
  data: {
    consumption: [65, 59, 80, 81, 56, 55],
    sectors: [30, 25, 20, 15, 10],
    labels: {
      consumption: ["Ene", "Feb", "Mar", "Abr", "May", "Jun"],
      sectors: ["Residencial", "Comercial", "Industrial", "Público", "Otros"],
    },
  },
}

// Clase principal del Dashboard
class Dashboard {
  constructor() {
    this.init()
  }

  init() {
    console.log("Inicializando Dashboard...")
    this.setupEventListeners()
    this.setupSidebar()
    this.initializeCharts()
    this.loadDashboardData()
    this.startRealTimeUpdates()
  }

  // Configurar event listeners
  setupEventListeners() {
    console.log("Configurando event listeners...")

    // Toggle sidebar desktop
    const sidebarToggle = document.getElementById("sidebarToggle")
    if (sidebarToggle) {
      sidebarToggle.addEventListener("click", (e) => {
        e.preventDefault()
        e.stopPropagation()
        console.log("Sidebar toggle clicked - Desktop")
        this.handleToggleClick()
      })
    }

    // Toggle sidebar móvil
    const mobileMenuToggle = document.getElementById("mobileMenuToggle")
    if (mobileMenuToggle) {
      mobileMenuToggle.addEventListener("click", (e) => {
        e.preventDefault()
        e.stopPropagation()
        console.log("Mobile menu toggle clicked")
        this.handleToggleClick()
      })
    }

    // Overlay móvil
    const mobileOverlay = document.getElementById("mobileOverlay")
    if (mobileOverlay) {
      mobileOverlay.addEventListener("click", (e) => {
        e.preventDefault()
        console.log("Mobile overlay clicked")
        this.closeMobileSidebar()
      })
    }

    // Navigation links
    const navLinks = document.querySelectorAll(".nav-link")
    navLinks.forEach((link, index) => {
      link.addEventListener("click", (e) => {
        console.log(`Nav link ${index} clicked:`, link.getAttribute("data-section"))
        this.handleNavigation(e)
      })
    })

    // Action cards
    const actionCards = document.querySelectorAll(".action-card")
    actionCards.forEach((card) => {
      card.addEventListener("click", () => this.handleQuickAction(card))
    })

    // Responsive handling
    window.addEventListener("resize", () => this.handleResize())

    // Logout button
    const logoutBtn = document.querySelector(".logout-btn")
    if (logoutBtn) {
      logoutBtn.addEventListener("click", (e) => {
        e.preventDefault()
        this.handleLogout()
      })
    }

    // Cerrar sidebar móvil al hacer clic fuera - MEJORADO
    document.addEventListener("click", (e) => {
      const sidebar = document.getElementById("sidebar")
      const mobileToggle = document.getElementById("mobileMenuToggle")
      const sidebarToggle = document.getElementById("sidebarToggle")

      // Solo cerrar si estamos en móvil, el sidebar está abierto, y el clic fue fuera del sidebar y botones
      if (
        this.isMobile() &&
        DashboardConfig.sidebar.isMobileOpen &&
        sidebar &&
        !sidebar.contains(e.target) &&
        (!mobileToggle || !mobileToggle.contains(e.target)) &&
        (!sidebarToggle || !sidebarToggle.contains(e.target))
      ) {
        console.log("Cerrando sidebar por clic fuera")
        this.closeMobileSidebar()
      }
    })
  }

  // Verificar si es móvil
  isMobile() {
    return window.innerWidth < 992
  }

  // Manejar click del toggle (unificado)
  handleToggleClick() {
    console.log("Handle toggle click - isMobile:", this.isMobile())

    if (this.isMobile()) {
      this.toggleMobileSidebar()
    } else {
      this.toggleDesktopSidebar()
    }
  }

  // Toggle sidebar desktop
  toggleDesktopSidebar() {
    console.log("Toggle desktop sidebar, current state:", DashboardConfig.sidebar.isCollapsed)

    if (DashboardConfig.sidebar.isCollapsed) {
      this.expandSidebar()
    } else {
      this.collapseSidebar()
    }
  }

  // Toggle sidebar móvil
  toggleMobileSidebar() {
    console.log("Toggle mobile sidebar, current state:", DashboardConfig.sidebar.isMobileOpen)

    if (DashboardConfig.sidebar.isMobileOpen) {
      this.closeMobileSidebar()
    } else {
      this.openMobileSidebar()
    }
  }

  // Configurar sidebar
  setupSidebar() {
    console.log("Configurando sidebar...")
    const sidebar = document.getElementById("sidebar")

    if (!sidebar) {
      console.error("Sidebar no encontrado")
      return
    }

    // Configuración inicial basada en el tamaño de pantalla
    if (this.isMobile()) {
      // En móvil, asegurar que esté cerrado inicialmente
      console.log("Configurando para móvil - cerrando sidebar")
      this.closeMobileSidebar()
      // Remover clases de desktop
      sidebar.classList.remove("collapsed")
      DashboardConfig.sidebar.isCollapsed = false
    } else {
      // En desktop, restaurar estado guardado
      console.log("Configurando para desktop")
      const savedState = localStorage.getItem("sidebarCollapsed")
      if (savedState === "true") {
        DashboardConfig.sidebar.isCollapsed = true
        this.collapseSidebar()
      }
      // Asegurar que no tenga clases de móvil
      sidebar.classList.remove("mobile-open")
      DashboardConfig.sidebar.isMobileOpen = false
    }
  }

  // Colapsar sidebar (desktop)
  collapseSidebar() {
    console.log("Colapsando sidebar...")
    const sidebar = document.getElementById("sidebar")

    if (sidebar) {
      sidebar.classList.add("collapsed")
      DashboardConfig.sidebar.isCollapsed = true
      localStorage.setItem("sidebarCollapsed", "true")
      this.updateSidebarTooltips(true)
    }
  }

  // Expandir sidebar (desktop)
  expandSidebar() {
    console.log("Expandiendo sidebar...")
    const sidebar = document.getElementById("sidebar")

    if (sidebar) {
      sidebar.classList.remove("collapsed")
      DashboardConfig.sidebar.isCollapsed = false
      localStorage.setItem("sidebarCollapsed", "false")
      this.updateSidebarTooltips(false)
    }
  }

  // Abrir sidebar móvil
  openMobileSidebar() {
    console.log("Abriendo sidebar móvil...")
    const sidebar = document.getElementById("sidebar")
    const overlay = document.getElementById("mobileOverlay")

    if (sidebar) {
      sidebar.classList.add("mobile-open")
      console.log("Clase mobile-open agregada")
    }
    if (overlay) {
      overlay.classList.add("active")
      console.log("Overlay activado")
    }

    DashboardConfig.sidebar.isMobileOpen = true
    document.body.style.overflow = "hidden"
    console.log("Estado móvil actualizado:", DashboardConfig.sidebar.isMobileOpen)
  }

  // Cerrar sidebar móvil
  closeMobileSidebar() {
    console.log("Cerrando sidebar móvil...")
    const sidebar = document.getElementById("sidebar")
    const overlay = document.getElementById("mobileOverlay")

    if (sidebar) {
      sidebar.classList.remove("mobile-open")
      console.log("Clase mobile-open removida")
    }
    if (overlay) {
      overlay.classList.remove("active")
      console.log("Overlay desactivado")
    }

    DashboardConfig.sidebar.isMobileOpen = false
    document.body.style.overflow = ""
    console.log("Estado móvil actualizado:", DashboardConfig.sidebar.isMobileOpen)
  }

  // Actualizar tooltips del sidebar
  updateSidebarTooltips(collapsed) {
    const navLinks = document.querySelectorAll(".nav-link")

    navLinks.forEach((link) => {
      const span = link.querySelector("span")
      if (span && collapsed) {
        link.setAttribute("title", span.textContent)
      } else {
        link.removeAttribute("title")
      }
    })
  }

  // Manejar navegación
  handleNavigation(e) {
    e.preventDefault()

    const link = e.currentTarget
    const section = link.getAttribute("data-section")
    const href = link.getAttribute("href")

    console.log("Navegando a:", section, href)

    // Si es un enlace directo, navegar
    if (href && href !== "#" && !href.startsWith("javascript:")) {
      window.location.href = href
      return
    }

    // Actualizar estado activo
    document.querySelectorAll(".nav-item").forEach((item) => {
      item.classList.remove("active")
    })

    link.parentElement.classList.add("active")

    // Actualizar título de página
    const pageTitle = document.querySelector(".page-title")
    const linkText = link.querySelector("span")?.textContent || "Dashboard"
    if (pageTitle) {
      pageTitle.textContent = linkText
    }

    // Cargar contenido de sección
    if (section) {
      this.loadSection(section)
    }

    // Cerrar sidebar móvil si está abierto
    if (this.isMobile()) {
      this.closeMobileSidebar()
    }
  }

  // Cargar sección
  loadSection(section) {
    const contentArea = document.getElementById("contentArea")

    if (!contentArea) return

    // Mostrar loading
    this.showLoading(contentArea)

    // Simular carga de contenido
    setTimeout(() => {
      switch (section) {
        case "dashboard":
          this.loadDashboardContent()
          break
        case "customers":
          this.loadCustomersContent()
          break
        case "billing":
          this.loadBillingContent()
          break
        case "operations":
          this.loadOperationsContent()
          break
        case "reports":
          this.loadReportsContent()
          break
        case "maintenance":
          this.loadMaintenanceContent()
          break
        case "inventory":
          this.loadInventoryContent()
          break
        case "settings":
          window.location.href = "settings.html"
          break
        default:
          this.loadDashboardContent()
      }
    }, 500)
  }

  // Mostrar loading
  showLoading(container) {
    container.innerHTML = `
      <div class="loading-container">
        <div class="loading-spinner">
          <i class="fas fa-spinner fa-spin"></i>
        </div>
        <p>Cargando...</p>
      </div>
    `
  }

  // Cargar contenido del dashboard
  loadDashboardContent() {
    // Restaurar contenido original del dashboard
    window.location.reload()
  }

  // Cargar contenido de clientes
  loadCustomersContent() {
    const contentArea = document.getElementById("contentArea")
    contentArea.innerHTML = `
      <div class="section-header">
        <h2>Gestión de Clientes</h2>
        <button class="btn btn-primary">
          <i class="fas fa-plus"></i> Nuevo Cliente
        </button>
      </div>
      <div class="customers-content">
        <div class="search-filters">
          <input type="text" placeholder="Buscar cliente..." class="form-control">
          <select class="form-select">
            <option>Todos los sectores</option>
            <option>Residencial</option>
            <option>Comercial</option>
            <option>Industrial</option>
          </select>
        </div>
        <div class="customers-table">
          <p class="text-center text-muted">Tabla de clientes se cargaría aquí...</p>
        </div>
      </div>
    `
  }

  // Cargar otros contenidos (placeholder)
  loadBillingContent() {
    this.loadPlaceholderContent("Facturación", "file-invoice-dollar")
  }

  loadOperationsContent() {
    this.loadPlaceholderContent("Operaciones", "cogs")
  }

  loadReportsContent() {
    this.loadPlaceholderContent("Reportes", "chart-bar")
  }

  loadMaintenanceContent() {
    this.loadPlaceholderContent("Mantenimiento", "tools")
  }

  loadInventoryContent() {
    this.loadPlaceholderContent("Inventario", "boxes")
  }

  // Cargar contenido placeholder
  loadPlaceholderContent(title, icon) {
    const contentArea = document.getElementById("contentArea")
    contentArea.innerHTML = `
      <div class="placeholder-content">
        <div class="placeholder-icon">
          <i class="fas fa-${icon}"></i>
        </div>
        <h3>${title}</h3>
        <p>Esta sección está en desarrollo. Pronto estará disponible con todas las funcionalidades.</p>
      </div>
    `
  }

  // Manejar acciones rápidas
  handleQuickAction(card) {
    const actionType = card.querySelector("h5")?.textContent || "Acción"

    // Efecto visual
    card.style.transform = "scale(0.95)"
    setTimeout(() => {
      card.style.transform = ""
    }, 150)

    // Mostrar notificación
    this.showNotification(`Acción: ${actionType}`, "info")
  }

  // Manejar logout
  handleLogout() {
    if (confirm("¿Estás seguro de que deseas cerrar sesión?")) {
      localStorage.removeItem("sidebarCollapsed")
      this.showNotification("Cerrando sesión...", "info", 1000)

      setTimeout(() => {
        window.location.href = "../index.html"
      }, 1000)
    }
  }

  // Inicializar gráficos
  initializeCharts() {
    if (typeof Chart !== "undefined") {
      this.initConsumptionChart()
      this.initSectorsChart()
    } else {
      console.log("Chart.js no disponible, saltando inicialización de gráficos")
    }
  }

  // Inicializar gráfico de consumo
  initConsumptionChart() {
    const ctx = document.getElementById("consumptionChart")
    if (!ctx) return

    // Destruir gráfico existente
    if (DashboardConfig.charts.consumption) {
      DashboardConfig.charts.consumption.destroy()
    }

    DashboardConfig.charts.consumption = new Chart(ctx, {
      type: "line",
      data: {
        labels: DashboardConfig.data.labels.consumption,
        datasets: [
          {
            label: "Consumo (Miles de litros)",
            data: DashboardConfig.data.consumption,
            borderColor: "#2196F3",
            backgroundColor: "rgba(33, 150, 243, 0.1)",
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: "#1976D2",
            pointBorderColor: "#fff",
            pointBorderWidth: 2,
            pointRadius: 6,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: "rgba(0, 0, 0, 0.1)",
            },
          },
          x: {
            grid: {
              display: false,
            },
          },
        },
        elements: {
          point: {
            hoverRadius: 8,
          },
        },
      },
    })
  }

  // Inicializar gráfico de sectores
  initSectorsChart() {
    const ctx = document.getElementById("sectorsChart")
    if (!ctx) return

    // Destruir gráfico existente
    if (DashboardConfig.charts.sectors) {
      DashboardConfig.charts.sectors.destroy()
    }

    DashboardConfig.charts.sectors = new Chart(ctx, {
      type: "doughnut",
      data: {
        labels: DashboardConfig.data.labels.sectors,
        datasets: [
          {
            data: DashboardConfig.data.sectors,
            backgroundColor: ["#2196F3", "#4CAF50", "#FF9800", "#F44336", "#9C27B0"],
            borderWidth: 0,
            hoverBorderWidth: 3,
            hoverBorderColor: "#fff",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              padding: 20,
              usePointStyle: true,
            },
          },
        },
        cutout: "60%",
      },
    })
  }

  // Cargar datos del dashboard
  loadDashboardData() {
    this.updateStats()
    this.updateRecentActivity()
    this.updateAlerts()
  }

  // Actualizar estadísticas
  updateStats() {
    const statNumbers = document.querySelectorAll(".stat-content h3")

    statNumbers.forEach((stat) => {
      const finalValue = stat.textContent
      this.animateNumber(stat, 0, this.parseStatValue(finalValue), 1000)
    })
  }

  // Parsear valor de estadística
  parseStatValue(value) {
    const numStr = value.replace(/[^\d.,]/g, "")
    return Number.parseFloat(numStr.replace(",", "")) || 0
  }

  // Animar números
  animateNumber(element, start, end, duration) {
    const startTime = performance.now()
    const originalText = element.textContent

    const animate = (currentTime) => {
      const elapsed = currentTime - startTime
      const progress = Math.min(elapsed / duration, 1)

      const current = start + (end - start) * this.easeOutQuart(progress)

      if (originalText.includes("$")) {
        element.textContent = "$" + this.formatNumber(Math.floor(current))
      } else if (originalText.includes("%")) {
        element.textContent = current.toFixed(1) + "%"
      } else {
        element.textContent = this.formatNumber(Math.floor(current))
      }

      if (progress < 1) {
        requestAnimationFrame(animate)
      }
    }

    requestAnimationFrame(animate)
  }

  // Función de easing
  easeOutQuart(t) {
    return 1 - Math.pow(1 - t, 4)
  }

  // Formatear número
  formatNumber(num) {
    return num.toLocaleString()
  }

  // Actualizar actividad reciente
  updateRecentActivity() {
    console.log("Actualizando actividad reciente...")
  }

  // Actualizar alertas
  updateAlerts() {
    console.log("Actualizando alertas del sistema...")
  }

  // Iniciar actualizaciones en tiempo real
  startRealTimeUpdates() {
    setInterval(() => {
      this.updateRealTimeData()
    }, 30000)
  }

  // Actualizar datos en tiempo real
  updateRealTimeData() {
    const badges = document.querySelectorAll(".badge")
    badges.forEach((badge) => {
      const currentValue = Number.parseInt(badge.textContent) || 0
      const newValue = Math.max(0, currentValue + Math.floor(Math.random() * 3) - 1)
      badge.textContent = newValue

      if (newValue !== currentValue) {
        badge.style.animation = "pulse 0.5s ease-in-out"
        setTimeout(() => {
          badge.style.animation = ""
        }, 500)
      }
    })
  }

  // Manejar redimensionamiento
  handleResize() {
    console.log("Redimensionando - isMobile:", this.isMobile())

    const sidebar = document.getElementById("sidebar")
    const overlay = document.getElementById("mobileOverlay")

    if (this.isMobile()) {
      // Cambió a móvil
      console.log("Cambiando a vista móvil")

      // Limpiar estados de desktop
      if (sidebar) {
        sidebar.classList.remove("collapsed")
      }
      DashboardConfig.sidebar.isCollapsed = false

      // Si el sidebar estaba visible, cerrarlo en móvil
      if (DashboardConfig.sidebar.isMobileOpen) {
        this.closeMobileSidebar()
      }
    } else {
      // Cambió a desktop
      console.log("Cambiando a vista desktop")

      // Limpiar estados de móvil
      if (sidebar) {
        sidebar.classList.remove("mobile-open")
      }
      if (overlay) {
        overlay.classList.remove("active")
      }
      DashboardConfig.sidebar.isMobileOpen = false
      document.body.style.overflow = ""

      // Restaurar estado de desktop
      const savedState = localStorage.getItem("sidebarCollapsed")
      if (savedState === "true") {
        this.collapseSidebar()
      }
    }

    // Redimensionar gráficos
    if (DashboardConfig.charts.consumption) {
      DashboardConfig.charts.consumption.resize()
    }
    if (DashboardConfig.charts.sectors) {
      DashboardConfig.charts.sectors.resize()
    }
  }

  // Mostrar notificación
  showNotification(message, type = "info", duration = 3000) {
    const notification = document.createElement("div")
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`
    notification.style.cssText = `
      top: 20px;
      right: 20px;
      z-index: 9999;
      min-width: 300px;
    `

    notification.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `

    document.body.appendChild(notification)

    setTimeout(() => {
      if (notification.parentNode) {
        notification.remove()
      }
    }, duration)
  }
}

// Función global de logout
function logout() {
  if (window.dashboardInstance) {
    window.dashboardInstance.handleLogout()
  } else {
    if (confirm("¿Estás seguro de que deseas cerrar sesión?")) {
      window.location.href = "../index.html"
    }
  }
}

console.log("Configurando inicialización del dashboard...")

// Función de inicialización más robusta
function initializeDashboard() {
  console.log("Función initializeDashboard ejecutándose...")

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
      console.log("DOM ready - creando instancia de Dashboard")
      window.dashboardInstance = new Dashboard()
    })
  } else {
    console.log("DOM ya está listo - creando instancia inmediatamente")
    window.dashboardInstance = new Dashboard()
  }
}

// Ejecutar inicialización
initializeDashboard()

// Agregar estilos adicionales
const additionalStyles = `
    .loading-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 300px;
      color: var(--medium-gray);
    }
    
    .loading-spinner {
      font-size: 2rem;
      margin-bottom: 1rem;
      color: var(--secondary-blue);
    }
    
    .placeholder-content {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 400px;
      text-align: center;
      color: var(--medium-gray);
    }
    
    .placeholder-icon {
      font-size: 4rem;
      margin-bottom: 1rem;
      color: var(--secondary-blue);
      opacity: 0.5;
    }
    
    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }
    
    .search-filters {
      display: flex;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    
    .search-filters .form-control,
    .search-filters .form-select {
      max-width: 250px;
    }
    
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.1); }
      100% { transform: scale(1); }
    }
  `

const styleSheet = document.createElement("style")
styleSheet.textContent = additionalStyles
document.head.appendChild(styleSheet)

// Manejar errores globales
window.addEventListener("error", (e) => {
  console.error("Error en dashboard:", e.error)
  if (window.dashboardInstance) {
    window.dashboardInstance.showNotification("Ha ocurrido un error inesperado", "danger")
  }
})
