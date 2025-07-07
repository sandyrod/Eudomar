// Authentication Forms Component Manager
class AuthForms {
  constructor(options = {}) {
    this.options = {
      containerId: "authFormsContainer",
      defaultForm: "loginForm",
      onLogin: null,
      onRegister: null,
      onForgotPassword: null,
      onFormSwitch: null,
      ...options,
    }

    this.currentForm = this.options.defaultForm
    this.isLoaded = false
    this.validators = {}
    this.Swal = window.Swal || null // Declare Swal variable

    this.init()
  }

  async init() {
    await this.loadAllForms()
    this.setupEventListeners()
    this.setupValidation()
    this.showForm(this.options.defaultForm)
  }

  async loadAllForms() {
    try {
      const container = document.getElementById(this.options.containerId)
      if (!container) {
        console.error("Auth forms container not found")
        return
      }

      // Load all form components
      const forms = [
        { id: "loginForm", file: "./components/login-form.html" },
        { id: "registerForm", file: "./components/register-form.html" },
        { id: "forgotPasswordForm", file: "./components/forgot-password-form.html" },
      ]

      let allFormsHTML = ""

      for (const form of forms) {
        try {
          const response = await fetch(form.file)
          const formHTML = await response.text()
          allFormsHTML += formHTML
        } catch (error) {
          console.error(`Error loading ${form.id}:`, error)
        }
      }

      container.innerHTML = allFormsHTML
      this.isLoaded = true
    } catch (error) {
      console.error("Error loading auth forms:", error)
    }
  }

  setupEventListeners() {
    if (!this.isLoaded) return

    // Form switching buttons
    document.addEventListener("click", (event) => {
      const formTarget = event.target.getAttribute("data-form")
      if (formTarget) {
        event.preventDefault()
        this.showForm(formTarget)
      }
    })

    // Password toggle buttons
    document.addEventListener("click", (event) => {
      if (event.target.closest(".password-toggle")) {
        event.preventDefault()
        const button = event.target.closest(".password-toggle")
        const targetId = button.getAttribute("data-target")
        this.togglePasswordVisibility(targetId)
      }
    })

    // Form submissions
    this.setupFormSubmissions()
  }

  setupFormSubmissions() {
    // Login form
    const loginForm = document.getElementById("loginFormElement")
    if (loginForm) {
      loginForm.addEventListener("submit", (event) => {
        event.preventDefault()
        this.handleLogin(event)
      })
    }

    // Register form
    const registerForm = document.getElementById("registerFormElement")
    if (registerForm) {
      registerForm.addEventListener("submit", (event) => {
        event.preventDefault()
        this.handleRegister(event)
      })
    }

    // Forgot password form
    const forgotForm = document.getElementById("forgotPasswordFormElement")
    if (forgotForm) {
      forgotForm.addEventListener("submit", (event) => {
        event.preventDefault()
        this.handleForgotPassword(event)
      })
    }
  }

  setupValidation() {
    // Email validation
    this.validators.email = (email) => {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      return emailRegex.test(email)
    }

    // Password validation
    this.validators.password = (password) => {
      return password.length >= 6
    }

    // Phone validation
    this.validators.phone = (phone) => {
      const phoneRegex = /^[+]?[1-9][\d]{0,15}$/
      return phoneRegex.test(phone.replace(/\s/g, ""))
    }

    // Name validation
    this.validators.name = (name) => {
      return name.trim().length >= 2
    }
  }

  showForm(formId) {
    if (!this.isLoaded) return

    // Hide all forms
    document.querySelectorAll(".auth-form").forEach((form) => {
      form.classList.remove("active")
    })

    // Show target form
    const targetForm = document.getElementById(formId)
    if (targetForm) {
      targetForm.classList.add("active")
      this.currentForm = formId

      // Clear any previous errors
      this.clearFormErrors(formId)

      // Call form switch callback
      if (this.options.onFormSwitch) {
        this.options.onFormSwitch(formId)
      }
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

  async handleLogin(event) {
    const form = event.target
    const submitButton = form.querySelector('button[type="submit"]')

    // Get form data
    const formData = {
      username: document.getElementById("loginUsername").value.trim(),
      password: document.getElementById("loginPassword").value,
      rememberMe: document.getElementById("rememberMe").checked,
    }

    // Validate form
    const errors = this.validateLoginForm(formData)
    if (Object.keys(errors).length > 0) {
      this.displayFormErrors("login", errors)
      return
    }

    // Show loading state
    this.setButtonLoading(submitButton, true)

    try {
      if (this.options.onLogin) {
        await this.options.onLogin(formData)
      } else {
        // Default login behavior
        await this.simulateApiCall()
        this.showSuccessMessage("Login exitoso", "Redirigiendo al dashboard...")
        setTimeout(() => {
          window.location.href = "dashboard.html"
        }, 1500)
      }
    } catch (error) {
      this.showErrorMessage("Error de login", error.message || "Credenciales inválidas")
    } finally {
      this.setButtonLoading(submitButton, false)
    }
  }

  async handleRegister(event) {
    const form = event.target
    const submitButton = form.querySelector('button[type="submit"]')

    // Get form data
    const formData = {
      firstName: document.getElementById("firstName").value.trim(),
      lastName: document.getElementById("lastName").value.trim(),
      email: document.getElementById("registerEmail").value.trim(),
      username: document.getElementById("username").value.trim(),
      phoneNumber: document.getElementById("phoneNumber").value.trim(),
      department: document.getElementById("departmentSelect").value,
      password: document.getElementById("registerPassword").value,
      confirmPassword: document.getElementById("confirmPassword").value,
    }

    // Validate form
    const errors = this.validateRegisterForm(formData)
    if (Object.keys(errors).length > 0) {
      this.displayFormErrors("register", errors)
      return
    }

    // Show loading state
    this.setButtonLoading(submitButton, true)

    try {
      if (this.options.onRegister) {
        await this.options.onRegister(formData)
      } else {
        // Default register behavior
        await this.simulateApiCall()
        this.showSuccessMessage("Registro exitoso", "Tu cuenta ha sido creada correctamente")
        setTimeout(() => {
          this.showForm("loginForm")
        }, 2000)
      }
    } catch (error) {
      this.showErrorMessage("Error de registro", error.message || "No se pudo crear la cuenta")
    } finally {
      this.setButtonLoading(submitButton, false)
    }
  }

  async handleForgotPassword(event) {
    const form = event.target
    const submitButton = form.querySelector('button[type="submit"]')

    // Get form data
    const formData = {
      email: document.getElementById("forgotPasswordEmail").value.trim(),
    }

    // Validate form
    const errors = this.validateForgotPasswordForm(formData)
    if (Object.keys(errors).length > 0) {
      this.displayFormErrors("forgotPassword", errors)
      return
    }

    // Show loading state
    this.setButtonLoading(submitButton, true)

    try {
      if (this.options.onForgotPassword) {
        await this.options.onForgotPassword(formData)
      } else {
        // Default forgot password behavior
        await this.simulateApiCall()
        this.showSuccessMessage("Enlace enviado", "Revisa tu correo electrónico")
        setTimeout(() => {
          this.showForm("loginForm")
        }, 2000)
      }
    } catch (error) {
      this.showErrorMessage("Error", error.message || "No se pudo enviar el enlace")
    } finally {
      this.setButtonLoading(submitButton, false)
    }
  }

  validateLoginForm(data) {
    const errors = {}

    if (!data.username) {
      errors.username = "El nombre de usuario es requerido"
    } else if (data.username.length < 3) {
      errors.username = "El nombre de usuario debe tener al menos 3 caracteres"
    }

    if (!data.password) {
      errors.password = "La contraseña es requerida"
    } else if (!this.validators.password(data.password)) {
      errors.password = "La contraseña debe tener al menos 6 caracteres"
    }

    return errors
  }

  validateRegisterForm(data) {
    const errors = {}

    if (!data.firstName) {
      errors.firstName = "El nombre es requerido"
    } else if (!this.validators.name(data.firstName)) {
      errors.firstName = "El nombre debe tener al menos 2 caracteres"
    }

    if (!data.lastName) {
      errors.lastName = "El apellido es requerido"
    } else if (!this.validators.name(data.lastName)) {
      errors.lastName = "El apellido debe tener al menos 2 caracteres"
    }

    if (!data.email) {
      errors.email = "El correo electrónico es requerido"
    } else if (!this.validators.email(data.email)) {
      errors.email = "Formato de correo electrónico inválido"
    }

    if (!data.username) {
      errors.username = "El nombre de usuario es requerido"
    } else if (data.username.length < 3) {
      errors.username = "El nombre de usuario debe tener al menos 3 caracteres"
    }

    if (!data.phoneNumber) {
      errors.phoneNumber = "El teléfono es requerido"
    } else if (!this.validators.phone(data.phoneNumber)) {
      errors.phoneNumber = "Formato de teléfono inválido"
    }

    if (!data.department) {
      errors.department = "Debe seleccionar un departamento"
    }

    if (!data.password) {
      errors.password = "La contraseña es requerida"
    } else if (!this.validators.password(data.password)) {
      errors.password = "La contraseña debe tener al menos 6 caracteres"
    }

    if (!data.confirmPassword) {
      errors.confirmPassword = "Debe confirmar la contraseña"
    } else if (data.password !== data.confirmPassword) {
      errors.confirmPassword = "Las contraseñas no coinciden"
    }

    return errors
  }

  validateForgotPasswordForm(data) {
    const errors = {}

    if (!data.email) {
      errors.email = "El correo electrónico es requerido"
    } else if (!this.validators.email(data.email)) {
      errors.email = "Formato de correo electrónico inválido"
    }

    return errors
  }

  displayFormErrors(formType, errors) {
    // Clear previous errors
    this.clearFormErrors()

    // Display new errors
    Object.keys(errors).forEach((field) => {
      let errorElementId = ""

      switch (formType) {
        case "login":
          errorElementId = field === "username" ? "loginUsernameError" : "loginPasswordError"
          break
        case "register":
          errorElementId = `${field}Error`
          break
        case "forgotPassword":
          errorElementId = "forgotPasswordEmailError"
          break
      }

      const errorElement = document.getElementById(errorElementId)
      if (errorElement) {
        errorElement.textContent = errors[field]
        errorElement.style.display = "block"
      }
    })
  }

  clearFormErrors(formId = null) {
    const selector = formId ? `#${formId} .invalid-feedback` : ".invalid-feedback"
    document.querySelectorAll(selector).forEach((element) => {
      element.textContent = ""
      element.style.display = "none"
    })
  }

  setButtonLoading(button, isLoading) {
    const btnText = button.querySelector(".btn-text")
    const btnLoader = button.querySelector(".btn-loader")

    if (isLoading) {
      btnText.style.display = "none"
      btnLoader.style.display = "inline-block"
      button.disabled = true
    } else {
      btnText.style.display = "inline-block"
      btnLoader.style.display = "none"
      button.disabled = false
    }
  }

  showSuccessMessage(title, message) {
    if (this.Swal) {
      this.Swal.fire({
        icon: "success",
        title: title,
        text: message,
        timer: 2000,
        showConfirmButton: false,
      })
    } else {
      alert(`${title}: ${message}`)
    }
  }

  showErrorMessage(title, message) {
    if (this.Swal) {
      this.Swal.fire({
        icon: "error",
        title: title,
        text: message,
      })
    } else {
      alert(`${title}: ${message}`)
    }
  }

  async simulateApiCall() {
    return new Promise((resolve) => {
      setTimeout(resolve, 1500)
    })
  }

  // Public methods
  getCurrentForm() {
    return this.currentForm
  }

  switchToForm(formId) {
    this.showForm(formId)
  }

  resetForm(formId) {
    const form = document.getElementById(`${formId}Element`)
    if (form) {
      form.reset()
      this.clearFormErrors(formId)
    }
  }

  setFormData(formId, data) {
    Object.keys(data).forEach((key) => {
      const input = document.getElementById(key)
      if (input) {
        input.value = data[key]
      }
    })
  }
}

// Export for use in other files
if (typeof module !== "undefined" && module.exports) {
  module.exports = AuthForms
}
