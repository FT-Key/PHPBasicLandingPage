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