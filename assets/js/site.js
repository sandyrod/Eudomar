console.log("Site.js cargado")

// Import SweetAlert2
const Swal = window.Swal

// Configuración global del sitio
const SiteConfig = {
  sidebar: {
    isMobileOpen: false,
    isCollapsed: false,
  },
}

// Función para detectar móvil
function isMobile() {
  return window.innerWidth < 992
}

// Función para actualizar tooltips
function updateTooltips() {
  const navLinks = document.querySelectorAll(".nav-link")
  const logoutBtn = document.querySelector(".logout-btn")

  navLinks.forEach((link) => {
    const span = link.querySelector("span")
    if (span) {
      if (SiteConfig.sidebar.isCollapsed && !isMobile()) {
        link.setAttribute("title", span.textContent.trim())
      } else {
        link.removeAttribute("title")
      }
    }
  })

  // Tooltip para logout
  if (logoutBtn) {
    if (SiteConfig.sidebar.isCollapsed && !isMobile()) {
      logoutBtn.setAttribute("title", "Cerrar Sesión")
    } else {
      logoutBtn.removeAttribute("title")
    }
  }
}

// Función para toggle del sidebar
function toggleSidebar() {
  console.log("Toggle sidebar - isMobile:", isMobile())

  if (isMobile()) {
    toggleMobileSidebar()
  } else {
    toggleDesktopSidebar()
  }
}

// Toggle móvil
function toggleMobileSidebar() {
  const sidebar = document.getElementById("sidebar")
  const overlay = document.getElementById("mobileOverlay")

  console.log("Toggle móvil - estado actual:", SiteConfig.sidebar.isMobileOpen)

  if (SiteConfig.sidebar.isMobileOpen) {
    // Cerrar
    sidebar?.classList.remove("mobile-open")
    overlay?.classList.remove("active")
    SiteConfig.sidebar.isMobileOpen = false
    document.body.style.overflow = ""
    console.log("Sidebar móvil cerrado")
  } else {
    // Abrir
    sidebar?.classList.add("mobile-open")
    overlay?.classList.add("active")
    SiteConfig.sidebar.isMobileOpen = true
    document.body.style.overflow = "hidden"
    console.log("Sidebar móvil abierto")
  }
}

// Toggle desktop
function toggleDesktopSidebar() {
  const sidebar = document.getElementById("sidebar")

  console.log("Toggle desktop - estado actual:", SiteConfig.sidebar.isCollapsed)

  if (SiteConfig.sidebar.isCollapsed) {
    sidebar?.classList.remove("collapsed")
    SiteConfig.sidebar.isCollapsed = false
    localStorage.setItem("sidebarCollapsed", "false")
    console.log("Sidebar desktop expandido")
  } else {
    sidebar?.classList.add("collapsed")
    SiteConfig.sidebar.isCollapsed = true
    localStorage.setItem("sidebarCollapsed", "true")
    console.log("Sidebar desktop colapsado")
  }

  // Actualizar tooltips después del toggle
  setTimeout(() => {
    updateTooltips()
  }, 100)
}

// Cerrar sidebar móvil
function closeMobileSidebar() {
  if (isMobile() && SiteConfig.sidebar.isMobileOpen) {
    const sidebar = document.getElementById("sidebar")
    const overlay = document.getElementById("mobileOverlay")

    sidebar?.classList.remove("mobile-open")
    overlay?.classList.remove("active")
    SiteConfig.sidebar.isMobileOpen = false
    document.body.style.overflow = ""
    console.log("Sidebar móvil cerrado por overlay")
  }
}

// Función de logout con SweetAlert2
function logout() {
  Swal.fire({
    title: "¿Cerrar Sesión?",
    text: "¿Estás seguro de que deseas cerrar sesión?",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#1976d2",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Sí, cerrar sesión",
    cancelButtonText: "Cancelar",
    reverseButtons: true,
    customClass: {
      popup: "swal-custom-popup",
      title: "swal-custom-title",
      confirmButton: "swal-custom-confirm",
      cancelButton: "swal-custom-cancel",
    },
  }).then((result) => {
    if (result.isConfirmed) {
      // Mostrar loading
      Swal.fire({
        title: "Cerrando sesión...",
        text: "Por favor espera un momento",
        icon: "info",
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading()
        },
      })

      console.log("Cerrando sesión...")
      localStorage.removeItem("sidebarCollapsed")

      setTimeout(() => {
        window.location.href = "../index.html"
      }, 1500)
    }
  })
}

// Configuración de Settings
class SettingsManager {
  constructor() {
    this.init()
  }

  init() {
    this.setupEventListeners()
    this.loadSettings()
  }

  setupEventListeners() {
    // Botones de guardar
    const saveButtons = document.querySelectorAll(".settings-actions .btn-primary")
    saveButtons.forEach((button) => {
      button.addEventListener("click", () => this.saveSettings())
    })

    // Switches de notificaciones
    const switches = document.querySelectorAll(".form-check-input")
    switches.forEach((switch_) => {
      switch_.addEventListener("change", () => this.handleSwitchChange(switch_))
    })

    // Botones de acción del sistema
    const systemButtons = document.querySelectorAll(".system-actions .btn")
    systemButtons.forEach((button) => {
      button.addEventListener("click", (e) => this.handleSystemAction(e))
    })
  }

  loadSettings() {
    const savedSettings = localStorage.getItem("systemSettings")
    if (savedSettings) {
      const settings = JSON.parse(savedSettings)
      this.applySettings(settings)
    }
  }

  saveSettings() {
    const settings = this.collectSettings()
    localStorage.setItem("systemSettings", JSON.stringify(settings))
    this.showNotification("Configuración guardada exitosamente", "success")
  }

  collectSettings() {
    const settings = {}
    const inputs = document.querySelectorAll(".settings-form input, .settings-form select, .settings-form textarea")
    inputs.forEach((input) => {
      if (input.type === "checkbox") {
        settings[input.id] = input.checked
      } else {
        settings[input.id] = input.value
      }
    })
    return settings
  }

  applySettings(settings) {
    Object.keys(settings).forEach((key) => {
      const element = document.getElementById(key)
      if (element) {
        if (element.type === "checkbox") {
          element.checked = settings[key]
        } else {
          element.value = settings[key]
        }
      }
    })
  }

  handleSwitchChange(switch_) {
    const label = switch_.closest(".notification-item, .maintenance-item")?.querySelector("h5")?.textContent
    if (label) {
      const status = switch_.checked ? "activado" : "desactivado"
      this.showNotification(`${label} ${status}`, "info", 2000)
    }
  }

  handleSystemAction(e) {
    const button = e.target.closest(".btn")
    const action = button.textContent.trim()

    switch (true) {
      case action.includes("Respaldo"):
        this.createBackup()
        break
      case action.includes("Cache"):
        this.clearCache()
        break
      case action.includes("Actualizaciones"):
        this.checkUpdates()
        break
      case action.includes("Reiniciar"):
        this.restartSystem()
        break
    }
  }

  createBackup() {
    Swal.fire({
      title: "Creando Respaldo",
      text: "Generando respaldo del sistema...",
      icon: "info",
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      customClass: {
        popup: "swal-custom-popup",
      },
      didOpen: () => {
        Swal.showLoading()
      },
    })

    setTimeout(() => {
      Swal.fire({
        title: "✅ Respaldo Completado",
        text: "El respaldo del sistema se ha creado exitosamente",
        icon: "success",
        confirmButtonColor: "#4caf50",
        confirmButtonText: "Entendido",
        customClass: {
          popup: "swal-custom-popup",
          confirmButton: "swal-custom-success",
        },
      })
    }, 3000)
  }

  clearCache() {
    Swal.fire({
      title: "Limpiando Cache",
      text: "Eliminando archivos temporales...",
      icon: "info",
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      customClass: {
        popup: "swal-custom-popup",
      },
      didOpen: () => {
        Swal.showLoading()
      },
    })

    setTimeout(() => {
      Swal.fire({
        title: "🧹 Cache Limpiado",
        text: "El cache del sistema se ha limpiado correctamente",
        icon: "success",
        confirmButtonColor: "#4caf50",
        confirmButtonText: "Perfecto",
        customClass: {
          popup: "swal-custom-popup",
          confirmButton: "swal-custom-success",
        },
      })
    }, 2000)
  }

  checkUpdates() {
    Swal.fire({
      title: "Verificando Actualizaciones",
      text: "Consultando servidor de actualizaciones...",
      icon: "info",
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      customClass: {
        popup: "swal-custom-popup",
      },
      didOpen: () => {
        Swal.showLoading()
      },
    })

    setTimeout(() => {
      Swal.fire({
        title: "🚀 Sistema Actualizado",
        text: "Tu sistema está ejecutando la última versión disponible",
        icon: "success",
        confirmButtonColor: "#4caf50",
        confirmButtonText: "Excelente",
        customClass: {
          popup: "swal-custom-popup",
          confirmButton: "swal-custom-success",
        },
      })
    }, 2500)
  }

  restartSystem() {
    Swal.fire({
      title: "⚠️ Reiniciar Sistema",
      html: `
        <p>Esta acción reiniciará completamente el sistema.</p>
        <p><strong>¿Estás seguro de que deseas continuar?</strong></p>
      `,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#ff9800",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Sí, reiniciar",
      cancelButtonText: "Cancelar",
      reverseButtons: true,
      customClass: {
        popup: "swal-custom-popup",
        title: "swal-custom-title",
        htmlContainer: "swal-custom-html",
        confirmButton: "swal-custom-warning",
        cancelButton: "swal-custom-cancel",
      },
    }).then((result) => {
      if (result.isConfirmed) {
        // Mostrar progreso de reinicio
        let timerInterval
        Swal.fire({
          title: "Reiniciando Sistema",
          html: "El sistema se reiniciará en <b></b> segundos.",
          timer: 3000,
          timerProgressBar: true,
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          icon: "info",
          customClass: {
            popup: "swal-custom-popup",
          },
          didOpen: () => {
            Swal.showLoading()
            const b = Swal.getHtmlContainer().querySelector("b")
            timerInterval = setInterval(() => {
              b.textContent = Math.ceil(Swal.getTimerLeft() / 1000)
            }, 100)
          },
          willClose: () => {
            clearInterval(timerInterval)
          },
        }).then((result) => {
          if (result.dismiss === Swal.DismissReason.timer) {
            window.location.reload()
          }
        })
      }
    })
  }

  showNotification(message, type = "info", duration = 4000) {
    let icon, color

    switch (type) {
      case "success":
        icon = "success"
        color = "#4caf50"
        break
      case "error":
        icon = "error"
        color = "#f44336"
        break
      case "warning":
        icon = "warning"
        color = "#ff9800"
        break
      case "info":
      default:
        icon = "info"
        color = "#2196f3"
        break
    }

    const Toast = Swal.mixin({
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: duration,
      timerProgressBar: true,
      customClass: {
        popup: "swal-toast-popup",
        title: "swal-toast-title",
      },
      didOpen: (toast) => {
        toast.addEventListener("mouseenter", Swal.stopTimer)
        toast.addEventListener("mouseleave", Swal.resumeTimer)
      },
    })

    Toast.fire({
      icon: icon,
      title: message,
      background: "#ffffff",
      color: "#343a40",
      iconColor: color,
    })
  }
}

// Función para animar las barras del gráfico
function animateChartBars() {
  const bars = document.querySelectorAll(".bar")

  bars.forEach((bar, index) => {
    // Obtener el width original del style attribute
    const originalStyle = bar.getAttribute("style")
    const targetWidth = originalStyle.match(/width:\s*([^;]+)/)?.[1] || "0%"

    // Resetear width
    bar.style.width = "0%"

    // Animar con delay
    setTimeout(() => {
      bar.style.width = targetWidth
    }, index * 300) // Aumentar delay para mejor efecto visual
  })
}

// Función para formatear números
function formatNumber(num) {
  return new Intl.NumberFormat("es-ES").format(num)
}

// Función para calcular porcentajes reales
function calculateRealPercentages() {
  const barItems = document.querySelectorAll(".bar-item")
  const values = []

  // Recopilar todos los valores
  barItems.forEach((item) => {
    const bar = item.querySelector(".bar")
    const rawValue = bar.getAttribute("data-value")
    if (rawValue) {
      values.push(Number.parseInt(rawValue.replace(/,/g, "")))
    }
  })

  // Encontrar el valor máximo
  const maxValue = Math.max(...values)

  // Actualizar los anchos de las barras
  barItems.forEach((item, index) => {
    const bar = item.querySelector(".bar")
    const rawValue = bar.getAttribute("data-value")
    if (rawValue) {
      const value = Number.parseInt(rawValue.replace(/,/g, ""))
      const percentage = (value / maxValue) * 100

      // Actualizar el style attribute manteniendo otras propiedades
      const currentStyle = bar.getAttribute("style") || ""
      const newStyle = currentStyle.replace(/width:\s*[^;]+/, `width: ${percentage.toFixed(1)}%`)
      bar.setAttribute("style", newStyle)

      console.log(`${item.querySelector(".bar-label").textContent}: ${value} = ${percentage.toFixed(1)}%`)
    }
  })
}

// Función para actualizar valores de las barras con formato
function updateBarValues() {
  const barItems = document.querySelectorAll(".bar-item")

  barItems.forEach((item) => {
    const bar = item.querySelector(".bar")
    const valueElement = item.querySelector(".bar-value")
    const rawValue = bar.getAttribute("data-value")

    if (rawValue && valueElement) {
      valueElement.textContent = formatNumber(Number.parseInt(rawValue))
    }
  })
}

// Inicialización cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  console.log("DOM listo - configurando eventos...")

  // Configurar sidebar
  setupSidebar()

  // Configurar settings si estamos en esa página
  if (document.querySelector(".settings-container")) {
    console.log("Inicializando Settings Manager...")
    window.settingsManager = new SettingsManager()
  }

  // Animar gráfico de barras si existe
  if (document.querySelector(".horizontal-bars")) {
    updateBarValues()
    calculateRealPercentages() // Calcular porcentajes reales primero
    setTimeout(() => {
      animateChartBars()
    }, 500)
  }

  console.log("Configuración completada")
})

// Función para configurar submenús
function setupSubmenus() {
  const submenuToggles = document.querySelectorAll(".submenu-toggle")

  submenuToggles.forEach((toggle) => {
    toggle.addEventListener("click", (e) => {
      e.preventDefault()

      const navItem = toggle.closest(".nav-item")
      const isOpen = navItem.classList.contains("open")

      // Cerrar todos los otros submenús
      document.querySelectorAll(".nav-item.has-submenu.open").forEach((item) => {
        if (item !== navItem) {
          item.classList.remove("open")
        }
      })

      // Toggle del submenú actual
      if (isOpen) {
        navItem.classList.remove("open")
        console.log("Submenú cerrado:", toggle.querySelector("span").textContent)
      } else {
        navItem.classList.add("open")
        console.log("Submenú abierto:", toggle.querySelector("span").textContent)
      }
    })
  })

  // Manejar clics en elementos del submenú
  const submenuLinks = document.querySelectorAll(".submenu-link")
  submenuLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault()

      // Remover active de todos los submenu links
      submenuLinks.forEach((l) => l.classList.remove("active"))

      // Agregar active al clickeado
      link.classList.add("active")

      console.log("Submenú seleccionado:", link.querySelector("span").textContent)
    })
  })
}

// Función para configurar el sidebar
function setupSidebar() {
  // Botón toggle desktop
  const sidebarToggle = document.getElementById("sidebarToggle")
  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", (e) => {
      e.preventDefault()
      console.log("Clic en sidebar toggle")
      toggleSidebar()
    })
    console.log("Event listener agregado a sidebarToggle")
  }

  // Botón toggle móvil
  const mobileMenuToggle = document.getElementById("mobileMenuToggle")
  if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener("click", (e) => {
      e.preventDefault()
      console.log("Clic en mobile menu toggle")
      toggleSidebar()
    })
    console.log("Event listener agregado a mobileMenuToggle")
  }

  // Overlay móvil
  const mobileOverlay = document.getElementById("mobileOverlay")
  if (mobileOverlay) {
    mobileOverlay.addEventListener("click", (e) => {
      e.preventDefault()
      console.log("Clic en overlay")
      closeMobileSidebar()
    })
    console.log("Event listener agregado a mobileOverlay")
  }

  // Botón de logout
  const logoutBtn = document.querySelector(".logout-btn")
  if (logoutBtn) {
    logoutBtn.addEventListener("click", (e) => {
      e.preventDefault()
      console.log("Clic en logout")
      logout()
    })
    console.log("Event listener agregado a logout")
  }

  // Cerrar al hacer clic fuera
  document.addEventListener("click", (e) => {
    const sidebar = document.getElementById("sidebar")
    const mobileToggle = document.getElementById("mobileMenuToggle")
    const sidebarToggle = document.getElementById("sidebarToggle")

    if (
      isMobile() &&
      SiteConfig.sidebar.isMobileOpen &&
      sidebar &&
      !sidebar.contains(e.target) &&
      (!mobileToggle || !mobileToggle.contains(e.target)) &&
      (!sidebarToggle || !sidebarToggle.contains(e.target))
    ) {
      console.log("Clic fuera del sidebar - cerrando")
      closeMobileSidebar()
    }
  })

  // Configuración inicial
  if (isMobile()) {
    console.log("Configuración inicial para móvil")
    const sidebar = document.getElementById("sidebar")
    sidebar?.classList.remove("mobile-open")
    SiteConfig.sidebar.isMobileOpen = false
  } else {
    console.log("Configuración inicial para desktop")
    const savedState = localStorage.getItem("sidebarCollapsed")
    if (savedState === "true") {
      const sidebar = document.getElementById("sidebar")
      sidebar?.classList.add("collapsed")
      SiteConfig.sidebar.isCollapsed = true
    }
  }

  // Configurar tooltips iniciales
  updateTooltips()

  // Configurar submenús
  setupSubmenus()
}

// Manejar resize
window.addEventListener("resize", () => {
  console.log("Resize detectado - isMobile:", isMobile())

  if (isMobile()) {
    // Cambió a móvil - limpiar estados de desktop
    const sidebar = document.getElementById("sidebar")
    sidebar?.classList.remove("collapsed")
    SiteConfig.sidebar.isCollapsed = false
  } else {
    // Cambió a desktop - limpiar estados de móvil
    closeMobileSidebar()
  }

  // Actualizar tooltips después del resize
  updateTooltips()
})

console.log("Site.js configurado")
