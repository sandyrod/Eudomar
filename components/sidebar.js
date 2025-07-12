// Sidebar Component JavaScript
class SidebarComponent {
  constructor(options = {}) {
    this.options = {
      userName: "Juan Pérez",
      userRole: "Administrador",
      currentPage: "dashboard",
      onLogout: null,
      onMenuClick: null,
      ...options,
    }

    this.isLoaded = false
    this.isCollapsed = false
    this.isMobileOpen = false
  }

  async load(containerId) {
    try {
      console.log("Cargando sidebar desde archivo...")
      const response = await fetch("../components/sidebar.php")
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const sidebarHTML = await response.text()
      const container = document.getElementById(containerId)

      if (container) {
        container.innerHTML = sidebarHTML
        this.isLoaded = true
        this.init()
        console.log("Sidebar HTML cargado exitosamente")
      } else {
        throw new Error(`Container del sidebar no encontrado: ${containerId}`)
      }
    } catch (error) {
      console.error("Error cargando sidebar:", error)
      throw error
    }
  }

  init() {
    if (!this.isLoaded) return

    console.log("Inicializando sidebar...")
    // this.updateUserInfo() // Comentado para no sobreescribir datos dinámicos de PHP
    this.setActivePage()
    this.setupEventListeners()
    this.setupSubmenus()
    this.restoreState()
    console.log("Sidebar inicializado correctamente")
  }

  setupEventListeners() {
    console.log("Configurando event listeners del sidebar...")

    // Toggle sidebar
    const sidebarToggle = document.getElementById("sidebarToggle")
    if (sidebarToggle) {
      sidebarToggle.addEventListener("click", (e) => {
        e.preventDefault()
        this.toggle()
      })
      console.log("Event listener del toggle configurado")
    }

    // Logout button
    const logoutBtn = document.getElementById("logoutBtn")
    if (logoutBtn) {
      logoutBtn.addEventListener("click", (e) => {
        e.preventDefault()
        this.handleLogout()
      })
      console.log("Event listener del logout configurado")
    }

    // Menu item clicks
    const menuLinks = document.querySelectorAll(".nav-link:not(.submenu-toggle), .submenu-link")
    menuLinks.forEach((link) => {
      link.addEventListener("click", (e) => {
        this.handleMenuClick(e)
      })
    })
    console.log(`Event listeners de menú configurados: ${menuLinks.length} enlaces`)
  }

  setupSubmenus() {
    console.log("Configurando submenús...")
    const submenuToggles = document.querySelectorAll(".submenu-toggle")

    submenuToggles.forEach((toggle) => {
      toggle.addEventListener("click", (e) => {
        e.preventDefault()
        this.toggleSubmenu(toggle)
      })
    })
    console.log(`Submenús configurados: ${submenuToggles.length}`)
  }

  toggleSubmenu(toggle) {
    const navItem = toggle.closest(".nav-item")
    const submenu = navItem.querySelector(".submenu")
    const arrow = navItem.querySelector(".submenu-arrow")

    // Cerrar otros submenús
    document.querySelectorAll(".nav-item.has-submenu").forEach((item) => {
      if (item !== navItem) {
        item.classList.remove("open")
        const otherSubmenu = item.querySelector(".submenu")
        const otherArrow = item.querySelector(".submenu-arrow")
        if (otherSubmenu) otherSubmenu.style.maxHeight = "0"
        if (otherArrow) otherArrow.style.transform = "rotate(0deg)"
      }
    })

    // Toggle current submenu
    if (navItem.classList.contains("open")) {
      navItem.classList.remove("open")
      submenu.style.maxHeight = "0"
      arrow.style.transform = "rotate(0deg)"
      console.log("Submenu cerrado")
    } else {
      navItem.classList.add("open")
      submenu.style.maxHeight = "300px"
      arrow.style.transform = "rotate(90deg)"
      console.log("Submenu abierto")
    }
  }

  toggle() {
    if (this.isMobile()) {
      this.toggleMobile()
    } else {
      this.toggleDesktop()
    }
  }

  toggleDesktop() {
    const sidebar = document.getElementById("sidebar")

    if (this.isCollapsed) {
      sidebar?.classList.remove("collapsed")
      this.isCollapsed = false
      localStorage.setItem("sidebarCollapsed", "false")
      console.log("Sidebar expandido")
    } else {
      sidebar?.classList.add("collapsed")
      this.isCollapsed = true
      localStorage.setItem("sidebarCollapsed", "true")
      console.log("Sidebar colapsado")
    }

    this.updateTooltips()
  }

  toggleMobile() {
    const sidebar = document.getElementById("sidebar")
    const overlay = document.getElementById("mobileOverlay")

    if (this.isMobileOpen) {
      sidebar?.classList.remove("mobile-open")
      overlay?.classList.remove("active")
      this.isMobileOpen = false
      document.body.style.overflow = ""
      console.log("Sidebar móvil cerrado")
    } else {
      sidebar?.classList.add("mobile-open")
      overlay?.classList.add("active")
      this.isMobileOpen = true
      document.body.style.overflow = "hidden"
      console.log("Sidebar móvil abierto")
    }
  }

  closeMobile() {
    if (this.isMobile() && this.isMobileOpen) {
      const sidebar = document.getElementById("sidebar")
      const overlay = document.getElementById("mobileOverlay")

      sidebar?.classList.remove("mobile-open")
      overlay?.classList.remove("active")
      this.isMobileOpen = false
      document.body.style.overflow = ""
      console.log("Sidebar móvil cerrado")
    }
  }

  isMobile() {
    return window.innerWidth < 992
  }

  updateTooltips() {
    const navLinks = document.querySelectorAll(".nav-link")
    const logoutBtn = document.querySelector(".logout-btn")

    navLinks.forEach((link) => {
      const span = link.querySelector("span")
      if (span) {
        if (this.isCollapsed && !this.isMobile()) {
          link.setAttribute("title", span.textContent.trim())
        } else {
          link.removeAttribute("title")
        }
      }
    })

    if (logoutBtn) {
      if (this.isCollapsed && !this.isMobile()) {
        logoutBtn.setAttribute("title", "Cerrar Sesión")
      } else {
        logoutBtn.removeAttribute("title")
      }
    }
  }

  updateUserInfo() {
    const userName = document.getElementById("userName")
    const userRole = document.getElementById("userRole")

    if (userName) userName.textContent = this.options.userName
    if (userRole) userRole.textContent = this.options.userRole
    console.log("Información de usuario actualizada")
  }

  setActivePage() {
    // Remover active de todos los items
    document.querySelectorAll(".nav-item").forEach((item) => {
      item.classList.remove("active")
    })

    // Activar el item correspondiente a la página actual
    const currentPageItem = document.querySelector(`[data-page="${this.options.currentPage}"]`)
    if (currentPageItem) {
      currentPageItem.classList.add("active")
      console.log(`Página activa marcada: ${this.options.currentPage}`)
    }
  }

  handleMenuClick(e) {
    const link = e.currentTarget
    const href = link.getAttribute("href")

    // Si es un enlace directo, permitir navegación
    if (href && href !== "#" && !href.startsWith("javascript:")) {
      console.log(`Navegando a: ${href}`)
      return // Permitir navegación normal
    }

    e.preventDefault()

    // Remover active de todos los items
    document.querySelectorAll(".nav-item, .submenu-item").forEach((item) => {
      item.classList.remove("active")
    })

    // Agregar active al item clickeado
    const parentItem = link.closest(".nav-item, .submenu-item")
    if (parentItem) {
      parentItem.classList.add("active")
    }

    // Cerrar sidebar móvil si está abierto
    if (this.isMobile()) {
      this.closeMobile()
    }

    // Callback personalizado
    if (this.options.onMenuClick) {
      const section = link.getAttribute("data-section")
      this.options.onMenuClick(section, e)
    }

    console.log(`Menú clickeado: ${link.getAttribute("data-section")}`)
  }

  handleLogout() {
    console.log("Logout solicitado")
    // SweetAlert2
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
          setTimeout(() => {
            window.location.href = '../logout.php';
          }, 1200);
        }
      });
    } else {
      if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        window.location.href = '../logout.php';
      }
    }
  }

  restoreState() {
    if (this.isMobile()) {
      const sidebar = document.getElementById("sidebar")
      sidebar?.classList.remove("mobile-open")
      this.isMobileOpen = false
    } else {
      const savedState = localStorage.getItem("sidebarCollapsed")
      if (savedState === "true") {
        const sidebar = document.getElementById("sidebar")
        sidebar?.classList.add("collapsed")
        this.isCollapsed = true
        console.log("Estado del sidebar restaurado: colapsado")
      }
    }

    this.updateTooltips()
  }

  handleResize() {
    if (this.isMobile()) {
      // Cambió a móvil - limpiar estados de desktop
      const sidebar = document.getElementById("sidebar")
      sidebar?.classList.remove("collapsed")
      this.isCollapsed = false
    } else {
      // Cambió a desktop - limpiar estados de móvil
      this.closeMobile()
    }

    this.updateTooltips()
  }

  // Métodos públicos
  setCurrentPage(page) {
    this.options.currentPage = page
    this.setActivePage()
  }

  setUserInfo(userName, userRole) {
    this.options.userName = userName
    this.options.userRole = userRole
    // this.updateUserInfo() // Comentado para no sobreescribir datos dinámicos de PHP
  }

  setActiveMenuItem(section) {
    document.querySelectorAll(".nav-item, .submenu-item").forEach((item) => {
      item.classList.remove("active")
    })

    const menuItem = document.querySelector(`[data-section="${section}"]`)
    if (menuItem) {
      const parentItem = menuItem.closest(".nav-item, .submenu-item")
      if (parentItem) {
        parentItem.classList.add("active")

        // Si es un submenu item, abrir el parent submenu
        const parentSubmenu = menuItem.closest(".submenu")
        if (parentSubmenu) {
          const parentNavItem = parentSubmenu.closest(".nav-item")
          if (parentNavItem) {
            parentNavItem.classList.add("open")
            parentSubmenu.style.maxHeight = "300px"
            const arrow = parentNavItem.querySelector(".submenu-arrow")
            if (arrow) arrow.style.transform = "rotate(90deg)"
          }
        }
      }
    }
    console.log(`Menú activo establecido: ${section}`)
  }
}

// Exportar para uso global
window.SidebarComponent = SidebarComponent
