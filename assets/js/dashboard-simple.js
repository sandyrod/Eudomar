console.log("Dashboard simple cargado")

// Configuración simple
const sidebarState = {
  isMobileOpen: false,
  isCollapsed: false,
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
      if (sidebarState.isCollapsed && !isMobile()) {
        link.setAttribute("title", span.textContent.trim())
      } else {
        link.removeAttribute("title")
      }
    }
  })

  // Tooltip para logout
  if (logoutBtn) {
    if (sidebarState.isCollapsed && !isMobile()) {
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

  console.log("Toggle móvil - estado actual:", sidebarState.isMobileOpen)

  if (sidebarState.isMobileOpen) {
    // Cerrar
    sidebar?.classList.remove("mobile-open")
    overlay?.classList.remove("active")
    sidebarState.isMobileOpen = false
    document.body.style.overflow = ""
    console.log("Sidebar móvil cerrado")
  } else {
    // Abrir
    sidebar?.classList.add("mobile-open")
    overlay?.classList.add("active")
    sidebarState.isMobileOpen = true
    document.body.style.overflow = "hidden"
    console.log("Sidebar móvil abierto")
  }
}

// Toggle desktop
function toggleDesktopSidebar() {
  const sidebar = document.getElementById("sidebar")

  console.log("Toggle desktop - estado actual:", sidebarState.isCollapsed)

  if (sidebarState.isCollapsed) {
    sidebar?.classList.remove("collapsed")
    sidebarState.isCollapsed = false
    localStorage.setItem("sidebarCollapsed", "false")
    console.log("Sidebar desktop expandido")
  } else {
    sidebar?.classList.add("collapsed")
    sidebarState.isCollapsed = true
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
  if (isMobile() && sidebarState.isMobileOpen) {
    const sidebar = document.getElementById("sidebar")
    const overlay = document.getElementById("mobileOverlay")

    sidebar?.classList.remove("mobile-open")
    overlay?.classList.remove("active")
    sidebarState.isMobileOpen = false
    document.body.style.overflow = ""
    console.log("Sidebar móvil cerrado por overlay")
  }
}

// Función de logout
function logout() {
  if (confirm("¿Estás seguro de que deseas cerrar sesión?")) {
    console.log("Cerrando sesión...")
    // Limpiar localStorage
    localStorage.removeItem("sidebarCollapsed")
    // Redirigir al login
    window.location.href = "../index.html"
  }
}

// Inicialización cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  console.log("DOM listo - configurando eventos...")

  // Botón toggle desktop
  const sidebarToggle = document.getElementById("sidebarToggle")
  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", (e) => {
      e.preventDefault()
      console.log("Clic en sidebar toggle")
      toggleSidebar()
    })
    console.log("Event listener agregado a sidebarToggle")
  } else {
    console.error("sidebarToggle no encontrado")
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
  } else {
    console.error("mobileMenuToggle no encontrado")
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
  } else {
    console.error("mobileOverlay no encontrado")
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
  } else {
    console.error("logoutBtn no encontrado")
  }

  // Cerrar al hacer clic fuera
  document.addEventListener("click", (e) => {
    const sidebar = document.getElementById("sidebar")
    const mobileToggle = document.getElementById("mobileMenuToggle")
    const sidebarToggle = document.getElementById("sidebarToggle")

    if (
      isMobile() &&
      sidebarState.isMobileOpen &&
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
    sidebarState.isMobileOpen = false
  } else {
    console.log("Configuración inicial para desktop")
    const savedState = localStorage.getItem("sidebarCollapsed")
    if (savedState === "true") {
      const sidebar = document.getElementById("sidebar")
      sidebar?.classList.add("collapsed")
      sidebarState.isCollapsed = true
    }
  }

  // Configurar tooltips iniciales
  updateTooltips()

  console.log("Configuración completada")
})

// Manejar resize
window.addEventListener("resize", () => {
  console.log("Resize detectado - isMobile:", isMobile())

  if (isMobile()) {
    // Cambió a móvil - limpiar estados de desktop
    const sidebar = document.getElementById("sidebar")
    sidebar?.classList.remove("collapsed")
    sidebarState.isCollapsed = false
  } else {
    // Cambió a desktop - limpiar estados de móvil
    closeMobileSidebar()
  }

  // Actualizar tooltips después del resize
  updateTooltips()
})

console.log("Dashboard simple configurado")
