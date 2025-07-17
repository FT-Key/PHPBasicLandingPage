function initContactForm() {
  const form = document.getElementById('contactForm');
  if (!form) {
    console.warn('No se encontró el formulario contactForm');
    return;
  }

  // Evitar múltiples listeners
  if (form.dataset.listenerAttached === 'true') return;
  form.dataset.listenerAttached = 'true';

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    console.log('[initContactForm] submit capturado');

    try {
      const inputs = form.querySelectorAll('input[required], textarea[required]');
      let isValid = true;

      inputs.forEach(input => {
        if (!validateField(input)) {
          isValid = false;
        }
      });

      if (isValid) {
        console.log('[initContactForm] formulario válido, enviando...');
        submitForm(form); // O submitFormHola(form) si estás probando esa función
      } else {
        console.log('[initContactForm] formulario inválido');
        showAlert('Por favor corrige los errores en el formulario', 'error');
      }

      return false;
    } catch (error) {
      console.error('[initContactForm] error en el submit handler:', error);
      showAlert('Error inesperado. Intenta de nuevo.', 'error');
    }
  });
}

function submitForm(form) {
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalText = submitBtn.innerHTML;

  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';

  const formData = new FormData(form);

  fetch('http://localhost:8000/api/solicitar-servicio', {
    method: 'POST',
    body: formData
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showAlert(data.message || 'Formulario enviado con éxito', 'success');
        form.reset();
        form.querySelectorAll('.is-valid, .is-invalid').forEach(field => {
          field.classList.remove('is-valid', 'is-invalid');
        });
        if (typeof grecaptcha !== 'undefined') grecaptcha.reset();
      } else {
        showAlert(data.message || 'Hubo un error al procesar tu solicitud.', 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showAlert('Error al enviar el formulario. Inténtalo de nuevo.', 'error');
    })
    .finally(() => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
    });
}

function submitFormHola(form) {
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalText = submitBtn.innerHTML;

  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Consultando...';

  fetch('http://localhost:8000/api/hola')
    .then(response => response.text())
    .then(data => {
      console.log('Respuesta de /api/hola:', data);
      mostrarMensajeEsquina(JSON.parse(data).mensaje || 'Respuesta vacía');
    })
    .catch(error => {
      console.error('Error al consultar /api/hola:', error);
      mostrarMensajeEsquina('Error en la consulta');
    })
    .finally(() => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
    });
}
function mostrarMensajeEsquina(mensaje) {
  let mensajeBox = document.getElementById('mensajeEsquina');

  if (!mensajeBox) {
    mensajeBox = document.createElement('div');
    mensajeBox.id = 'mensajeEsquina';
    mensajeBox.style.position = 'fixed';
    mensajeBox.style.bottom = '20px';
    mensajeBox.style.right = '20px';
    mensajeBox.style.padding = '12px 20px';
    mensajeBox.style.backgroundColor = '#007bff';
    mensajeBox.style.color = '#fff';
    mensajeBox.style.borderRadius = '8px';
    mensajeBox.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
    mensajeBox.style.zIndex = '9999';
    mensajeBox.style.fontSize = '16px';
    document.body.appendChild(mensajeBox);
  }

  mensajeBox.textContent = mensaje;

  // Ocultar después de 5 segundos
  setTimeout(() => {
    if (mensajeBox) mensajeBox.remove();
  }, 5000);
}
