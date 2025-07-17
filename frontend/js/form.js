function initContactForm() {
  const form = document.getElementById('contactForm');
  if (!form) {
    console.warn('No se encontró el formulario contactForm');
    return;
  }

  // Evitar múltiples listeners
  if (form.dataset.listenerAttached === 'true') return;
  form.dataset.listenerAttached = 'true';

  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    try {
      const inputs = form.querySelectorAll('input[required], textarea[required]');
      let isValid = true;

      inputs.forEach(input => {
        if (!validateField(input)) {
          isValid = false;
        }
      });

      if (isValid) {
        await submitForm(form); // ✅ ahora espera a que termine
      } else {
        console.log('[initContactForm] formulario inválido');
        showAlert('Por favor corrige los errores en el formulario', 'error');
      }
    } catch (error) {
      console.error('[initContactForm] error en el submit handler:', error);
      showAlert('Error inesperado. Intenta de nuevo.', 'error');
    }
  });
}

async function submitForm(form) {

  const submitBtn = form.querySelector('button[type="submit"]');
  const originalText = submitBtn.innerHTML;

  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';

  try {
    const formData = new FormData(form);

    const response = await fetch('http://localhost:8000/api/solicitar-servicio', {
      method: 'POST',
      body: formData
    });

    const contentType = response.headers.get('content-type') || '';
    if (!contentType.includes('application/json')) {
      const text = await response.text();
      console.warn('[submitForm] Respuesta inesperada (no JSON):\n', text);
      showAlert('El servidor devolvió una respuesta no válida.', 'error');
      return;
    }

    const data = await response.json();

    if (data.success) {
      showAlert(data.message || 'Formulario enviado con éxito.', 'success');
      form.reset();
      form.querySelectorAll('.is-valid, .is-invalid').forEach(field => {
        field.classList.remove('is-valid', 'is-invalid');
      });
    } else {
      showAlert(data.message || 'Error al procesar el formulario.', 'error');
    }

  } catch (error) {
    console.error('[submitForm] Error en fetch:', error);
    showAlert('Error de red o del servidor.', 'error');
  } finally {
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalText;
  }
}