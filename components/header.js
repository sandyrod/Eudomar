// Header Component JavaScript
class HeaderComponent {
  constructor(options = {}) {
    this.options = {
      title: "Dashboard",
      showSearch: true,
      searchPlaceholder: "Buscar...",
      notificationsCount: 3,
      onSearch: null,
      onNotifications: null,
      onUserMenu: null,
      ...options,
    }

    this.isLoaded = false
  }

  async load(containerId) {
    try {
      console.log("Cargando header desde archivo...")
      const response = await fetch("../components/header.html")
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const headerHTML = await response.text()
      const container = document.getElementById(containerId)

      if (container) {
        container.innerHTML = headerHTML
        this.isLoaded = true
        this.init()
        console.log("Header HTML cargado exitosamente")
      } else {
        throw new Error(`Container del header no encontrado: ${containerId}`)
      }
    } catch (error) {
      console.error("Error cargando header:", error)
      throw error
    }
  }

  init() {
    if (!this.isLoaded) return

    console.log("Inicializando header...")
    this.updateTitle()
    this.updateBadges()
    this.setupSearch()
    this.setupEventListeners()
    this.setupDropdowns()
    console.log("Header inicializado correctamente")
  }

  setupEventListeners() {
    console.log("Configurando event listeners del header...")

    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById("mobileMenuToggle")
    if (mobileMenuToggle) {
      mobileMenuToggle.addEventListener("click", (e) => {
        e.preventDefault()
        if (window.sidebarComponent) {
          window.sidebarComponent.toggle()
        }
      })
      console.log("Event listener del mobile toggle configurado")
    }

    // Notifications dropdown
    const notificationsBtn = document.getElementById("notificationsBtn")
    if (notificationsBtn) {
      notificationsBtn.addEventListener("click", (e) => {
        e.preventDefault()
        e.stopPropagation()
        this.toggleDropdown("notificationsMenu")
      })
      console.log("Event listener de notificaciones configurado")
    }

    // User menu dropdown
    const userMenuToggle = document.getElementById("userMenuToggle")
    if (userMenuToggle) {
      userMenuToggle.addEventListener("click", (e) => {
        e.preventDefault()
        e.stopPropagation()
        this.toggleDropdown("userMenu")
      })
      console.log("Event listener del menú de usuario configurado")
    }

    // Download buttons in notifications
    const downloadBtns = document.querySelectorAll(".download-btn")
    downloadBtns.forEach((btn) => {
      btn.addEventListener("click", (e) => {
        e.preventDefault()
        e.stopPropagation()
        this.handleDownload(btn)
      })
    })
    console.log(`${downloadBtns.length} botones de descarga configurados`)

    // User menu items
    this.setupUserMenuItems()
  }

  setupUserMenuItems() {
    // Profile menu item
    const profileMenuItem = document.getElementById("profileMenuItem")
    if (profileMenuItem) {
      profileMenuItem.addEventListener("click", (e) => {
        e.preventDefault()
        this.handleProfile()
      })
    }

    // Settings menu item
    const settingsMenuItem = document.getElementById("settingsMenuItem")
    if (settingsMenuItem) {
      settingsMenuItem.addEventListener("click", (e) => {
        e.preventDefault()
        this.handleSettings()
      })
    }

    // Help menu item
    const helpMenuItem = document.getElementById("helpMenuItem")
    if (helpMenuItem) {
      helpMenuItem.addEventListener("click", (e) => {
        e.preventDefault()
        this.handleHelp()
      })
    }

    // Logout menu item
    const logoutMenuItem = document.getElementById("logoutMenuItem")
    if (logoutMenuItem) {
      logoutMenuItem.addEventListener("click", (e) => {
        e.preventDefault()
        this.handleLogout()
      })
    }

    console.log("Items del menú de usuario configurados")
  }

  setupDropdowns() {
    // Cerrar dropdowns al hacer click fuera
    document.addEventListener("click", (e) => {
      const dropdowns = document.querySelectorAll(".dropdown-menu.show")
      dropdowns.forEach((dropdown) => {
        const dropdownContainer = dropdown.closest(".dropdown")
        if (dropdownContainer && !dropdownContainer.contains(e.target)) {
          dropdown.classList.remove("show")
        }
      })
    })

    console.log("Sistema de dropdowns configurado")
  }

  toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId)
    if (!dropdown) return

    // Cerrar otros dropdowns
    const allDropdowns = document.querySelectorAll(".dropdown-menu.show")
    allDropdowns.forEach((dd) => {
      if (dd.id !== dropdownId) {
        dd.classList.remove("show")
      }
    })

    // Toggle el dropdown actual
    dropdown.classList.toggle("show")

    console.log(`Dropdown ${dropdownId} ${dropdown.classList.contains("show") ? "abierto" : "cerrado"}`)
  }

  setupSearch() {
    if (!this.options.showSearch) {
      const searchBox = document.getElementById("searchBox")
      if (searchBox) {
        searchBox.style.display = "none"
      }
      console.log("Búsqueda ocultada")
      return
    }

    const searchInput = document.getElementById("headerSearch")
    if (searchInput) {
      searchInput.placeholder = this.options.searchPlaceholder

      searchInput.addEventListener("input", (e) => {
        if (this.options.onSearch) {
          this.options.onSearch(e.target.value)
        }
      })

      searchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
          if (this.options.onSearch) {
            this.options.onSearch(e.target.value)
          }
        }
      })
      console.log("Búsqueda configurada")
    }
  }

  updateTitle() {
    const pageTitle = document.getElementById("pageTitle")
    if (pageTitle) {
      pageTitle.textContent = this.options.title
      console.log(`Título actualizado: ${this.options.title}`)
    }
  }

  updateBadges() {
    const notificationsBadge = document.getElementById("notificationsBadge")

    if (notificationsBadge) {
      notificationsBadge.textContent = this.options.notificationsCount
      if (this.options.notificationsCount === 0) {
        notificationsBadge.style.display = "none"
      } else {
        notificationsBadge.style.display = "inline-block"
      }
    }

    console.log("Badges actualizados")
  }

  handleDownload(btn) {
    const notificationItem = btn.closest(".notification-item")
    if (!notificationItem) return

    const reportName = notificationItem.querySelector("h6").textContent
    console.log(`Descargando reporte: ${reportName}`)

    // Simular descarga
    this.showNotification(`Descargando: ${reportName}`, "success")

    // Aquí iría la lógica real de descarga
    // Por ejemplo: window.open('/api/download/report?id=123', '_blank')
  }

  handleProfile() {
    console.log("Abriendo perfil de usuario...")
    this.showNotification("Perfil de usuario en desarrollo", "info")
    this.closeAllDropdowns()
  }

  handleSettings() {
    console.log("Navegando a configuración...")
    window.location.href = "../views/settings.html"
    this.closeAllDropdowns()
  }

  handleHelp() {
    console.log("Abriendo ayuda...")
    this.showNotification("Sistema de ayuda en desarrollo", "info")
    this.closeAllDropdowns()
  }

  handleLogout() {
    console.log("Cerrando sesión desde header...")
    this.closeAllDropdowns()

    if (typeof window.Swal !== "undefined") {
      window.Swal.fire({
        title: "¿Cerrar sesión?",
        text: "¿Estás seguro de que quieres cerrar sesión?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, cerrar sesión",
        cancelButtonText: "Cancelar",
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "../index.html"
        }
      })
    } else {
      if (confirm("¿Estás seguro de que quieres cerrar sesión?")) {
        window.location.href = "../index.html"
      }
    }
  }

  closeAllDropdowns() {
    const dropdowns = document.querySelectorAll(".dropdown-menu.show")
    dropdowns.forEach((dropdown) => {
      dropdown.classList.remove("show")
    })
  }

  showNotification(message, type = "info") {
    if (typeof window.Swal !== "undefined") {
      const Toast = window.Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
      })

      Toast.fire({
        icon: type,
        title: message,
      })
    } else {
      console.log(`${type.toUpperCase()}: ${message}`)
    }
  }

  // Métodos públicos
  setTitle(title) {
    this.options.title = title
    this.updateTitle()
  }

  updateNotificationsBadge(count) {
    this.options.notificationsCount = count
    const badge = document.getElementById("notificationsBadge")
    if (badge) {
      badge.textContent = count
      badge.style.display = count === 0 ? "none" : "inline-block"
    }
  }

  hideSearch() {
    const searchBox = document.getElementById("searchBox")
    if (searchBox) {
      searchBox.style.display = "none"
    }
  }

  showSearch() {
    const searchBox = document.getElementById("searchBox")
    if (searchBox) {
      searchBox.style.display = "flex"
    }
  }

  addNotification(notification) {
    const notificationsList = document.querySelector(".notifications-list")
    if (!notificationsList) return

    const notificationHTML = `
      <div class="notification-item">
        <div class="notification-icon">
          <i class="${notification.icon || "fas fa-file-alt"}"></i>
        </div>
        <div class="notification-content">
          <h6>${notification.title}</h6>
          <p>${notification.description}</p>
          <small>${notification.time}</small>
        </div>
        <button class="download-btn" title="Descargar" type="button">
          <i class="fas fa-download"></i>
        </button>
      </div>
    `

    notificationsList.insertAdjacentHTML("afterbegin", notificationHTML)

    // Actualizar contador
    this.updateNotificationsBadge(this.options.notificationsCount + 1)
  }
}

// Exportar para uso global
window.HeaderComponent = HeaderComponent
