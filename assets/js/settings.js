// Configuración de la página de settings
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
    // Cargar configuraciones guardadas
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

    // Recopilar todos los inputs
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
    this.showNotification("Creando respaldo del sistema...", "info")
    // Simular proceso de respaldo
    setTimeout(() => {
      this.showNotification("Respaldo creado exitosamente", "success")
    }, 3000)
  }

  clearCache() {
    this.showNotification("Limpiando cache del sistema...", "info")
    setTimeout(() => {
      this.showNotification("Cache limpiado exitosamente", "success")
    }, 2000)
  }

  checkUpdates() {
    this.showNotification("Verificando actualizaciones...", "info")
    setTimeout(() => {
      this.showNotification("Sistema actualizado a la última versión", "success")
    }, 2500)
  }

  restartSystem() {
    if (confirm("¿Estás seguro de que deseas reiniciar el sistema?")) {
      this.showNotification("Reiniciando sistema...", "warning")
      setTimeout(() => {
        window.location.reload()
      }, 3000)
    }
  }

  showNotification(message, type = "info", duration = 4000) {
    const notification = document.createElement("div")
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`
    notification.style.cssText = `
      top: 20px;
      right: 20px;
      z-index: 9999;
      min-width: 350px;
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

// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  window.settingsManager = new SettingsManager()

  // Inicializar también el dashboard para el sidebar
  const Dashboard =
    window.Dashboard ||
    (() => {}) // Declare Dashboard variable or use a placeholder // Declare Dashboard variable or use a placeholder
  if (window.dashboardInstance) {
    window.dashboardInstance = new Dashboard()
  }
})
