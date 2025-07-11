// ====================================
// FUNCIONALIDAD JAVASCRIPT FRONTEND
// ====================================

// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function () {
  initializeApp();
});

function initializeApp() {
  // Inicializar todas las funcionalidades
  initSmoothScrolling();
  initNavbarScroll();
  initFormValidation();
  initAnimations();
  initLoadingEffects();
  initContactForm();
  initScrollToTop();
  initParallaxEffects();
}

// ====================================
// NAVEGACIÓN SUAVE Y EFECTOS
// ====================================

function initSmoothScrolling() {
  // Navegación suave entre secciones
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const targetId = this.getAttribute('href');
      const targetElement = document.querySelector(targetId);

      if (targetElement) {
        const offsetTop = targetElement.offsetTop - 80; // Ajustar por navbar fijo
        window.scrollTo({
          top: offsetTop,
          behavior: 'smooth'
        });
      }
    });
  });
}

function initNavbarScroll() {
  // Cambiar apariencia del navbar al hacer scroll
  const navbar = document.querySelector('.navbar');

  window.addEventListener('scroll', function () {
    if (window.scrollY > 50) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }

    // Actualizar elemento activo en el navbar
    updateActiveNavItem();
  });
}

function updateActiveNavItem() {
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

  let currentSection = '';
  sections.forEach(section => {
    const sectionTop = section.offsetTop - 100;
    const sectionHeight = section.clientHeight;

    if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
      currentSection = section.getAttribute('id');
    }
  });

  navLinks.forEach(link => {
    link.classList.remove('active');
    if (link.getAttribute('href') === `#${currentSection}`) {
      link.classList.add('active');
    }
  });
}

// ====================================
// VALIDACIÓN DE FORMULARIOS
// ====================================

function initFormValidation() {
  const form = document.querySelector('form');
  const inputs = form.querySelectorAll('input, textarea, select');

  // Validación en tiempo real
  inputs.forEach(input => {
    input.addEventListener('blur', function () {
      validateField(this);
    });

    input.addEventListener('input', function () {
      if (this.classList.contains('is-invalid')) {
        validateField(this);
      }
    });
  });
}

function validateField(field) {
  const value = field.value.trim();
  const fieldType = field.type;
  const isRequired = field.hasAttribute('required');

  // Remover clases anteriores
  field.classList.remove('is-valid', 'is-invalid');

  // Validar campo requerido
  if (isRequired && value === '') {
    showFieldError(field, 'Este campo es obligatorio');
    return false;
  }

  // Validar email
  if (fieldType === 'email' && value !== '') {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(value)) {
      showFieldError(field, 'Por favor ingresa un email válido');
      return false;
    }
  }

  // Validar mensaje mínimo
  if (field.id === 'mensaje' && value.length < 10) {
    showFieldError(field, 'El mensaje debe tener al menos 10 caracteres');
    return false;
  }

  // Si llegamos aquí, el campo es válido
  showFieldSuccess(field);
  return true;
}

function showFieldError(field, message) {
  field.classList.add('is-invalid');

  // Remover mensaje anterior
  const existingError = field.parentNode.querySelector('.invalid-feedback');
  if (existingError) {
    existingError.remove();
  }

  // Crear nuevo mensaje de error
  const errorDiv = document.createElement('div');
  errorDiv.className = 'invalid-feedback';
  errorDiv.textContent = message;
  field.parentNode.appendChild(errorDiv);
}

function showFieldSuccess(field) {
  field.classList.add('is-valid');

  // Remover mensaje de error
  const existingError = field.parentNode.querySelector('.invalid-feedback');
  if (existingError) {
    existingError.remove();
  }
}

// ====================================
// PROCESAMIENTO DEL FORMULARIO
// ====================================

function initContactForm() {
  const form = document.querySelector('form');

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    // Validar todos los campos
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
      if (!validateField(input)) {
        isValid = false;
      }
    });

    if (isValid) {
      submitForm(form);
    } else {
      showAlert('Por favor corrige los errores en el formulario', 'error');
    }
  });
}

function submitForm(form) {
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalText = submitBtn.innerHTML;

  // Mostrar loading
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';

  // Crear FormData
  const formData = new FormData(form);

  // Enviar datos con fetch
  fetch('../php/procesar.php', {
    method: 'POST',
    body: formData
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showAlert(data.message, 'success');
        form.reset();
        // Remover clases de validación
        form.querySelectorAll('.is-valid, .is-invalid').forEach(field => {
          field.classList.remove('is-valid', 'is-invalid');
        });
      } else {
        showAlert(data.message, 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showAlert('Error al enviar el formulario. Inténtalo de nuevo.', 'error');
    })
    .finally(() => {
      // Restaurar botón
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
    });
}

// ====================================
// ANIMACIONES Y EFECTOS
// ====================================

function initAnimations() {
  // Animaciones al hacer scroll
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animate-in');
      }
    });
  }, observerOptions);

  // Observar elementos para animar
  document.querySelectorAll('.card, .row, .col-lg-6').forEach(el => {
    observer.observe(el);
  });
}

function initLoadingEffects() {
  const images = document.querySelectorAll('img');
  images.forEach(img => {
    if (img.complete) {
      img.classList.add('loaded');
    } else {
      img.addEventListener('load', function () {
        this.classList.add('loaded');
      });
    }
  });
}

function initParallaxEffects() {
  // Efecto parallax sutil en hero section
  const heroSection = document.querySelector('.hero-section');

  window.addEventListener('scroll', function () {
    if (window.scrollY < window.innerHeight) {
      const scrolled = window.scrollY;
      const parallax = scrolled * 0.5;
      heroSection.style.transform = `translateY(${parallax}px)`;
    }
  });
}

// ====================================
// BOTÓN SCROLL TO TOP
// ====================================

function initScrollToTop() {
  // Crear botón scroll to top
  const scrollBtn = document.createElement('button');
  scrollBtn.innerHTML = '<i class="bi bi-arrow-up"></i>';
  scrollBtn.className = 'scroll-to-top';
  scrollBtn.setAttribute('aria-label', 'Volver arriba');
  document.body.appendChild(scrollBtn);

  // Mostrar/ocultar botón
  window.addEventListener('scroll', function () {
    if (window.scrollY > 300) {
      scrollBtn.classList.add('show');
    } else {
      scrollBtn.classList.remove('show');
    }
  });

  // Función del botón
  scrollBtn.addEventListener('click', function () {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
}

// ====================================
// UTILIDADES Y ALERTAS
// ====================================

function showAlert(message, type = 'info') {
  // Crear alerta personalizada
  const alertDiv = document.createElement('div');
  alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
  alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';

  alertDiv.innerHTML = `
        <strong>${type === 'success' ? '¡Éxito!' : type === 'error' ? 'Error:' : 'Información:'}</strong>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

  document.body.appendChild(alertDiv);

  // Auto-remover después de 5 segundos
  setTimeout(() => {
    if (alertDiv.parentNode) {
      alertDiv.remove();
    }
  }, 5000);
}

function mostrarAlerta() {
  showAlert('¡Gracias por tu interés! Te contactaremos pronto.', 'success');
}

// ====================================
// OPTIMIZACIÓN DE RENDIMIENTO
// ====================================

// Throttle para eventos de scroll
function throttle(func, limit) {
  let inThrottle;
  return function () {
    const args = arguments;
    const context = this;
    if (!inThrottle) {
      func.apply(context, args);
      inThrottle = true;
      setTimeout(() => inThrottle = false, limit);
    }
  }
}

// Aplicar throttle a eventos costosos
window.addEventListener('scroll', throttle(function () {
  // Eventos de scroll optimizados
}, 16)); // ~60fps

// ====================================
// PRELOADER (OPCIONAL)
// ====================================

window.addEventListener('load', function () {
  const preloader = document.querySelector('.preloader');
  if (preloader) {
    preloader.style.opacity = '0';
    setTimeout(() => {
      preloader.style.display = 'none';
    }, 300);
  }
});

console.log('TechSolutions - JavaScript cargado correctamente ✅');