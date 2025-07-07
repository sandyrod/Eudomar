// Dashboard Components Loader
class DashboardLoader {
  constructor() {
    this.componentsLoaded = {
      sidebar: false,
      header: false,
      overlay: false,
    }
    this.allLoaded = false
  }

  async loadAll() {
    console.log("Iniciando carga de componentes del dashboard...")

    try {
      // Cargar componentes en paralelo
      await Promise.all([this.loadSidebar(), this.loadHeader(), this.loadMobileOverlay()])

      this.allLoaded = true
      console.log("Todos los componentes cargados exitosamente")

      // Configurar eventos globales
      this.setupGlobalEvents()

      return true
    } catch (error) {
      console.error("Error cargando componentes:", error)
      return false
    }
  }

  async loadSidebar() {
    try {
      console.log("Cargando sidebar...")
      const currentPage = this.getCurrentPage()

      // Verificar que la clase SidebarComponent esté disponible
      if (typeof window.SidebarComponent === "undefined") {
        throw new Error("SidebarComponent no está disponible")
      }

      window.sidebarComponent = new window.SidebarComponent({
        currentPage: currentPage,
        onLogout: () => {
          if (typeof window.logout === "function") {
            window.logout()
          }
        },
      })

      await window.sidebarComponent.load("sidebarContainer")
      this.componentsLoaded.sidebar = true
      console.log("Sidebar cargado exitosamente")
    } catch (error) {
      console.error("Error cargando sidebar:", error)
      throw error
    }
  }

  async loadHeader() {
    try {
      console.log("Cargando header...")
      const pageTitle = this.getPageTitle()

      // Verificar que la clase HeaderComponent esté disponible
      if (typeof window.HeaderComponent === "undefined") {
        throw new Error("HeaderComponent no está disponible")
      }

      window.headerComponent = new window.HeaderComponent({
        title: pageTitle,
        showSearch: this.shouldShowSearch(),
        onSearch: (query) => {
          console.log("Búsqueda:", query)
        },
        onNotifications: () => {
          console.log("Notificaciones clickeadas")
        },
        onMessages: () => {
          console.log("Mensajes clickeados")
        },
      })

      await window.headerComponent.load("headerContainer")
      this.componentsLoaded.header = true
      console.log("Header cargado exitosamente")
    } catch (error) {
      console.error("Error cargando header:", error)
      throw error
    }
  }

  async loadMobileOverlay() {
    try {
      console.log("Cargando mobile overlay...")
      const response = await fetch("../components/mobile-overlay.html")
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const overlayHTML = await response.text()
      const container = document.getElementById("mobileOverlayContainer")

      if (container) {
        container.innerHTML = overlayHTML
        this.componentsLoaded.overlay = true
        console.log("Mobile overlay cargado exitosamente")
      } else {
        console.warn("Container mobileOverlayContainer no encontrado")
      }
    } catch (error) {
      console.error("Error cargando mobile overlay:", error)
      // No lanzar error aquí porque el overlay no es crítico
    }
  }

  setupGlobalEvents() {
    console.log("Configurando eventos globales...")

    // Event listener para cerrar sidebar móvil al hacer clic en overlay
    const mobileOverlay = document.getElementById("mobileOverlay")
    if (mobileOverlay && window.sidebarComponent) {
      mobileOverlay.addEventListener("click", () => {
        window.sidebarComponent.closeMobile()
      })
    }

    // Event listener para cerrar sidebar móvil al hacer clic fuera
    document.addEventListener("click", (e) => {
      if (!window.sidebarComponent) return

      const sidebar = document.getElementById("sidebar")
      const mobileToggle = document.getElementById("mobileMenuToggle")
      const sidebarToggle = document.getElementById("sidebarToggle")

      if (
        window.sidebarComponent.isMobile() &&
        window.sidebarComponent.isMobileOpen &&
        sidebar &&
        !sidebar.contains(e.target) &&
        (!mobileToggle || !mobileToggle.contains(e.target)) &&
        (!sidebarToggle || !sidebarToggle.contains(e.target))
      ) {
        window.sidebarComponent.closeMobile()
      }
    })

    // Event listener para resize
    window.addEventListener("resize", () => {
      if (window.sidebarComponent) {
        window.sidebarComponent.handleResize()
      }
    })

    console.log("Eventos globales configurados")
  }

  getCurrentPage() {
    const path = window.location.pathname
    if (path.includes("settings")) return "settings"
    if (path.includes("dashboard")) return "dashboard"
    return "dashboard"
  }

  getPageTitle() {
    const path = window.location.pathname
    if (path.includes("settings")) return "Configuración del Sistema"
    if (path.includes("dashboard")) return "Resumen de Gestión"
    return "Dashboard"
  }

  shouldShowSearch() {
    const path = window.location.pathname
    return path.includes("settings") // Solo mostrar búsqueda en settings
  }

  isReady() {
    return this.allLoaded
  }

  // Métodos públicos para actualizar componentes
  updatePageTitle(title) {
    if (window.headerComponent) {
      window.headerComponent.setTitle(title)
    }
  }

  setActiveMenuItem(section) {
    if (window.sidebarComponent) {
      window.sidebarComponent.setActiveMenuItem(section)
    }
  }

  updateNotifications(count) {
    if (window.headerComponent) {
      window.headerComponent.updateNotificationsBadge(count)
    }
  }

  updateMessages(count) {
    if (window.headerComponent) {
      window.headerComponent.updateMessagesBadge(count)
    }
  }
}

// Exportar para uso global
window.DashboardLoader = DashboardLoader
