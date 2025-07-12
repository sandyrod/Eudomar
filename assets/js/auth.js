// Configuración global
const CONFIG = {
  forms: {
    login: "loginForm",
    register: "registerForm",
    forgot: "forgotPasswordForm",
  },
  validation: {
    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    password: /^.{6,}$/, // Simplificado para testing
    phone: /^(\+58|0058|58)?[-\s]?[24]\d{2}[-\s]?\d{3}[-\s]?\d{4}$/,
  },
  messages: {
    required: "Este campo es requerido",
    email: "Por favor ingresa un correo electrónico válido",
    password: "La contraseña debe tener al menos 6 caracteres",
    passwordMatch: "Las contraseñas no coinciden",
    phone: "Por favor ingresa un número de teléfono válido",
    terms: "Debes aceptar los términos y condiciones",
  },
  notifications: {
    duration: 5000,
  },
  // Credenciales de prueba
  testCredentials: {
    username: "admin",
    password: "admin123",
  },
}

// Estado global de la aplicación
const AppState = {
  currentForm: "loginForm",
  isLoading: false,
  validationErrors: {},
  formsLoaded: false,
}

// Clase principal de la aplicación
class AuthApp {
  constructor() {
    this.initialized = false
  }

  init() {
    if (this.initialized) return

    console.log("Inicializando AuthApp...")
    this.setupEventListeners()
    this.setupPasswordToggles()
    this.setupFormValidation()
    this.setupInputAnimations()
    this.initialized = true
    console.log("AuthApp inicializada correctamente")
  }

  // Configurar event listeners
  setupEventListeners() {
    console.log("Configurando event listeners...")

    // Esperar a que los formularios estén cargados
    const waitForForms = () => {
      const loginForm = document.getElementById("loginFormElement")
      const registerForm = document.getElementById("registerFormElement")
      const forgotForm = document.getElementById("forgotPasswordFormElement")

      if (!loginForm || !registerForm || !forgotForm) {
        console.log("Formularios no encontrados, reintentando...")
        setTimeout(waitForForms, 100)
        return
      }

      console.log("Formularios encontrados, configurando listeners...")

      // Formularios
      if (loginForm) {
        loginForm.addEventListener("submit", (e) => this.handleLogin(e))
        console.log("Event listener agregado al formulario de login")
      }

      if (registerForm) {
        registerForm.addEventListener("submit", (e) => this.handleRegister(e))
        console.log("Event listener agregado al formulario de registro")
      }

      if (forgotForm) {
        forgotForm.addEventListener("submit", (e) => this.handleForgotPassword(e))
        console.log("Event listener agregado al formulario de recuperación")
      }

      // Validación en tiempo real
      this.setupRealTimeValidation()
      console.log("Event listeners configurados correctamente")
    }

    waitForForms()
  }

  // Configurar validación en tiempo real
  setupRealTimeValidation() {
    const inputs = document.querySelectorAll(".form-input")

    inputs.forEach((input) => {
      // Validar al perder el foco
      input.addEventListener("blur", () => {
        this.validateField(input)
      })

      // Limpiar errores al escribir (con delay)
      input.addEventListener("input", () => {
        if (input.classList.contains("is-invalid")) {
          clearTimeout(input.validationTimeout)
          input.validationTimeout = setTimeout(() => {
            this.validateField(input)
          }, 500)
        }
      })

      // Animaciones de label
      input.addEventListener("focus", () => {
        this.animateLabel(input, true)
      })

      input.addEventListener("blur", () => {
        if (!input.value.trim()) {
          this.animateLabel(input, false)
        }
      })
    })
  }

  // Configurar toggles de contraseña
  setupPasswordToggles() {
    // Los toggles ya están manejados por FormLoader
    console.log("Password toggles manejados por FormLoader")
  }

  // Configurar validación de formularios
  setupFormValidation() {
    // Esperar a que los elementos estén disponibles
    setTimeout(() => {
      const confirmPassword = document.getElementById("confirmPassword")
      const registerPassword = document.getElementById("registerPassword")

      if (confirmPassword && registerPassword) {
        confirmPassword.addEventListener("input", () => {
          if (confirmPassword.value && registerPassword.value) {
            if (confirmPassword.value !== registerPassword.value) {
              this.showFieldError(confirmPassword, CONFIG.messages.passwordMatch)
            } else {
              this.clearFieldError(confirmPassword)
              this.markFieldAsValid(confirmPassword)
            }
          }
        })
      }
    }, 500)
  }

  // Configurar animaciones de input
  setupInputAnimations() {
    setTimeout(() => {
      const inputs = document.querySelectorAll(".form-input")

      inputs.forEach((input) => {
        // Verificar si ya tiene valor al cargar
        if (input.value.trim()) {
          this.animateLabel(input, true)
        }
      })
    }, 500)
  }

  // Validar campo individual
  validateField(field) {
    const value = field.value.trim()
    const fieldId = field.id
    let isValid = true
    let errorMessage = ""

    // Limpiar estado previo
    this.clearFieldError(field)

    // Validación de campos requeridos
    if (field.hasAttribute("required") && !value) {
      errorMessage = CONFIG.messages.required
      isValid = false
    }

    // Validaciones específicas
    if (value && isValid) {
      switch (field.type) {
        case "email":
          if (!CONFIG.validation.email.test(value)) {
            errorMessage = CONFIG.messages.email
            isValid = false
          }
          break

        case "password":
          if (!CONFIG.validation.password.test(value)) {
            errorMessage = CONFIG.messages.password
            isValid = false
          }
          break

        case "tel":
          if (value.length < 10) {
            errorMessage = CONFIG.messages.phone
            isValid = false
          }
          break
      }

      // Validación de confirmación de contraseña
      if (fieldId === "confirmPassword") {
        const password = document.getElementById("registerPassword")?.value
        if (value !== password) {
          errorMessage = CONFIG.messages.passwordMatch
          isValid = false
        }
      }
    }

    // Validación de select
    if (field.tagName === "SELECT" && !value) {
      errorMessage = CONFIG.messages.required
      isValid = false
    }

    // Mostrar resultado
    if (!isValid) {
      this.showFieldError(field, errorMessage)
    } else if (value || field.type === "checkbox") {
      this.markFieldAsValid(field)
    }

    return isValid
  }

  // Validar formulario completo
  validateForm(form) {
    const inputs = form.querySelectorAll(".form-input[required]")
    let isValid = true

    inputs.forEach((input) => {
      if (!input.value.trim()) {
        this.showFieldError(input, CONFIG.messages.required)
        isValid = false
      } else {
        this.clearFieldError(input)
      }
    })

    return isValid
  }

  // Mostrar error en campo
  showFieldError(field, message) {
    field.classList.remove("is-valid")
    field.classList.add("is-invalid")

    const errorElement = document.getElementById(field.id + "Error")
    if (errorElement) {
      errorElement.textContent = message
    }

    // Animación de shake
    field.style.animation = "shake 0.5s ease-in-out"
    setTimeout(() => {
      field.style.animation = ""
    }, 500)
  }

  // Limpiar error de campo
  clearFieldError(field) {
    field.classList.remove("is-invalid")

    const errorElement = document.getElementById(field.id + "Error")
    if (errorElement) {
      errorElement.textContent = ""
    }
  }

  // Marcar campo como válido
  markFieldAsValid(field) {
    field.classList.remove("is-invalid")
    field.classList.add("is-valid")
    this.clearFieldError(field)
  }

  // Animar label
  animateLabel(input, focused) {
    const label = input.parentNode.querySelector(".input-label")
    if (label) {
      if (focused || input.value.trim()) {
        label.style.transform = "translateY(-2.2rem) scale(0.85)"
        label.style.color = focused ? "var(--secondary-blue)" : "var(--medium-gray)"
      } else {
        label.style.transform = "translateY(-50%) scale(1)"
        label.style.color = "var(--medium-gray)"
      }
    }
  }

  // Mostrar estado de carga
  showLoading(formId) {
    const form = document.getElementById(formId)
    const submitBtn = form?.querySelector('button[type="submit"]')

    if (submitBtn) {
      submitBtn.classList.add("loading")
      submitBtn.disabled = true
      AppState.isLoading = true
    }
  }

  // Ocultar estado de carga
  hideLoading(formId) {
    const form = document.getElementById(formId)
    const submitBtn = form?.querySelector('button[type="submit"]')

    if (submitBtn) {
      submitBtn.classList.remove("loading")
      submitBtn.disabled = false
      AppState.isLoading = false
    }
  }

  // Mostrar notificación con SweetAlert2
  showNotification(message, type = "success", duration = CONFIG.notifications.duration) {
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

    const Toast = window.Swal.mixin({
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
        toast.addEventListener("mouseenter", window.Swal.stopTimer)
        toast.addEventListener("mouseleave", window.Swal.resumeTimer)
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

  // Manejar login
  async handleLogin(e) {
    e.preventDefault();
    console.log("Procesando login...");

    const form = e.target;
    const username = document.getElementById("loginUsername").value.trim();
    const password = document.getElementById("loginPassword").value;
    const remember = document.getElementById("rememberMe").checked;

    // Validación básica
    if (!username) {
      this.showFieldError(document.getElementById("loginUsername"), "El nombre de usuario es requerido");
      return;
    }
    if (!password) {
      this.showFieldError(document.getElementById("loginPassword"), "La contraseña es requerida");
      return;
    }

    this.showLoading("loginForm");

    try {
      const response = await fetch('../login_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          loginUsername: username,
          loginPassword: password,
        })
      });
      const result = await response.json();

      if (result.success) {
        this.showNotification(result.message || "¡Bienvenido! Iniciando sesión...", "success");
        // Guardar estado si se marcó recordar
        if (remember) {
          localStorage.setItem("rememberUser", "true");
          localStorage.setItem("username", username);
        }
        setTimeout(() => {
          window.location.href = "views/dashboard.php";
        }, 1200);
      } else {
        // Mostrar errores de campos si existen
        if (result.errors) {
          Object.entries(result.errors).forEach(([field, message]) => {
            const input = document.getElementById(field);
            if (input) this.showFieldError(input, message);
          });
        }
        this.showNotification(result.message || "Usuario o contraseña incorrectos.", "error");
      }
    } catch (error) {
      console.error("Error en login:", error);
      this.showNotification("Error al iniciar sesión. Por favor intenta nuevamente.", "error");
    } finally {
      this.hideLoading("loginForm");
    }
  }

  // Manejar registro
  async handleRegister(e) {
    e.preventDefault()
    console.log("Procesando registro...")

    const form = e.target
    if (!this.validateForm(form)) {
      this.showNotification("Por favor corrige los errores en el formulario", "error")
      return
    }

    this.showLoading("registerForm")

    // Map frontend fields to backend expected fields
    const formData = {
      firstName: document.getElementById("firstName").value.trim(),
      lastName: document.getElementById("lastName").value.trim(),
      registerEmail: document.getElementById("registerEmail").value.trim(),
      username: document.getElementById("username").value.trim(),
      phoneNumber: document.getElementById("phoneNumber").value.trim(),
      departmentSelect: document.getElementById("departmentSelect").value,
      registerPassword: document.getElementById("registerPassword").value,
    }

    try {
      const response = await fetch('../register_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData),
      })
      const result = await response.json()

      if (result.success) {
        this.showNotification(result.message || "¡Cuenta creada exitosamente!", "success")
        setTimeout(() => {
          if (window.formLoader) {
            window.formLoader.switchToForm("loginForm")
          }
        }, 2000)
        form.reset()
        // Limpiar estados de validación
        form.querySelectorAll('.form-input').forEach(input => {
          input.classList.remove('is-valid', 'is-invalid')
        })
      } else {
        // Mostrar errores de campos si existen
        if (result.errors) {
          Object.entries(result.errors).forEach(([field, message]) => {
            const input = document.getElementById(field)
            if (input) this.showFieldError(input, message)
          })
        }
        this.showNotification(result.message || "No se pudo crear la cuenta.", "error")
      }
    } catch (error) {
      console.error("Error en registro:", error)
      this.showNotification("Error al crear la cuenta. Por favor intenta nuevamente.", "error")
    } finally {
      this.hideLoading("registerForm")
    }
  }

  // Manejar recuperación de contraseña
  async handleForgotPassword(e) {
    e.preventDefault()
    console.log("Procesando recuperación de contraseña...")

    const form = e.target
    const email = document.getElementById("forgotPasswordEmail").value.trim()

    if (!email) {
      this.showFieldError(document.getElementById("forgotPasswordEmail"), "El correo electrónico es requerido")
      return
    }

    this.showLoading("forgotPasswordForm")

    try {
      // Simular llamada a API
      await this.simulateApiCall(1500)

      console.log("Recuperación de contraseña para:", email)

      this.showNotification("¡Enlace enviado! Revisa tu correo electrónico.", "success")

      setTimeout(() => {
        if (window.formLoader) {
          window.formLoader.switchToForm("loginForm")
        }
      }, 2000)
    } catch (error) {
      console.error("Error en recuperación:", error)
      this.showNotification("Error al enviar el enlace. Por favor intenta nuevamente.", "error")
    } finally {
      this.hideLoading("forgotPasswordForm")
    }
  }

  // Simular llamada a API
  simulateApiCall(delay) {
    return new Promise((resolve) => {
      setTimeout(resolve, delay)
    })
  }
}

// Inicializar aplicación cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  console.log("DOM cargado, creando instancia de AuthApp...")
  window.authApp = new AuthApp()

  // La inicialización se hará cuando los formularios estén cargados
})

// Manejar errores globales
window.addEventListener("error", (e) => {
  console.error("Error global:", e.error)
  if (window.authApp) {
    window.authApp.showNotification("Ha ocurrido un error inesperado", "error")
  }
})

// Función de debug para testing
window.debugAuth = () => {
  console.log("=== DEBUG AUTH ===")
  console.log("Estado actual:", AppState)
  console.log("Credenciales de prueba:", CONFIG.testCredentials)
  console.log("Formulario actual:", document.getElementById(AppState.currentForm))
  console.log("==================")
}
