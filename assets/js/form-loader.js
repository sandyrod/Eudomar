// Form Loader Component
class FormLoader {
  constructor() {
    this.formsLoaded = false
    this.init()
  }

  async init() {
    console.log("Inicializando FormLoader...")
    await this.loadAllForms()
    this.setupEventListeners()
    console.log("FormLoader inicializado correctamente")
  }

  async loadAllForms() {
    try {
      const container = document.getElementById("authFormsContainer")
      if (!container) {
        console.error("Container authFormsContainer no encontrado")
        return
      }

      console.log("Cargando formularios...")

      // Cargar todos los formularios
      const forms = [
        { id: "loginForm", file: "./components/login-form.html" },
        { id: "registerForm", file: "./components/register-form.html" },
        { id: "forgotPasswordForm", file: "./components/forgot-password-form.html" },
      ]

      let allFormsHTML = ""

      for (const form of forms) {
        try {
          console.log(`Cargando ${form.id}...`)
          const response = await fetch(form.file)
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
          }
          const formHTML = await response.text()
          allFormsHTML += formHTML
          console.log(`${form.id} cargado exitosamente`)
        } catch (error) {
          console.error(`Error cargando ${form.id}:`, error)
        }
      }

      container.innerHTML = allFormsHTML
      this.formsLoaded = true
      console.log("Todos los formularios cargados")
    } catch (error) {
      console.error("Error cargando formularios:", error)
    }
  }

  setupEventListeners() {
    if (!this.formsLoaded) {
      console.log("Formularios no cargados aún, reintentando...")
      setTimeout(() => this.setupEventListeners(), 100)
      return
    }

    console.log("Configurando event listeners...")

    // Event listeners para cambio de formularios
    document.addEventListener("click", (event) => {
      const formTarget = event.target.getAttribute("data-form")
      if (formTarget) {
        event.preventDefault()
        console.log("Cambiando a formulario:", formTarget)
        this.showForm(formTarget)
      }
    })

    // Event listeners para toggles de contraseña
    document.addEventListener("click", (event) => {
      if (event.target.closest(".password-toggle")) {
        event.preventDefault()
        const button = event.target.closest(".password-toggle")
        const targetId = button.getAttribute("data-target")
        this.togglePasswordVisibility(targetId)
      }
    })

    console.log("Event listeners configurados")
  }

  showForm(formId) {
    console.log("Mostrando formulario:", formId)

    // Ocultar todos los formularios
    document.querySelectorAll(".auth-form").forEach((form) => {
      form.classList.remove("active")
    })

    // Mostrar el formulario objetivo
    const targetForm = document.getElementById(formId)
    if (targetForm) {
      targetForm.classList.add("active")
      console.log("Formulario mostrado:", formId)

      // Limpiar errores previos
      this.clearFormErrors(formId)

      // Focus en el primer input
      setTimeout(() => {
        const firstInput = targetForm.querySelector('.form-input:not([type="checkbox"])')
        if (firstInput) {
          firstInput.focus()
        }
      }, 300)
    } else {
      console.error("Formulario no encontrado:", formId)
    }
  }

  togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId)
    const button = document.querySelector(`[data-target="${inputId}"]`)

    if (input && button) {
      const icon = button.querySelector("i")

      if (input.type === "password") {
        input.type = "text"
        icon.classList.remove("fa-eye")
        icon.classList.add("fa-eye-slash")
      } else {
        input.type = "password"
        icon.classList.remove("fa-eye-slash")
        icon.classList.add("fa-eye")
      }
    }
  }

  clearFormErrors(formId) {
    const form = document.getElementById(formId)
    if (form) {
      const errorElements = form.querySelectorAll(".invalid-feedback")
      errorElements.forEach((error) => {
        error.textContent = ""
        error.style.display = "none"
      })

      const inputs = form.querySelectorAll(".form-input")
      inputs.forEach((input) => {
        input.classList.remove("is-invalid", "is-valid")
      })
    }
  }

  // Método público para cambiar formularios
  switchToForm(formId) {
    this.showForm(formId)
  }

  // Método para verificar si los formularios están cargados
  isReady() {
    return this.formsLoaded
  }
}

// Exportar para uso global
window.FormLoader = FormLoader
