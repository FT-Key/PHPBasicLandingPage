import { CONFIG } from '../constants/config.js';
import { showAlert } from './utilities.js';
import { validateField } from './validations.js';

export function initContactForm() {
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

export async function submitForm(form) {
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalText = submitBtn.innerHTML;

  submitBtn.disabled = true;
  submitBtn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';

  try {
    const formData = new FormData(form);

    // ✅ 1. Obtener el token del reCAPTCHA
    const token = grecaptcha.getResponse();

    // ✅ 2. Validar si fue completado
    if (!token) {
      showAlert('Por favor completá el reCAPTCHA antes de enviar.', 'error');
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
      return;
    }

    // ✅ 3. Adjuntar el token al formData (clave obligatoria: "g-recaptcha-response")
    formData.append('g-recaptcha-response', token);

    const response = await fetch(`${CONFIG.API_BASE_URL}/solicitar-servicio`, {
      method: 'POST',
      body: formData,
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
      grecaptcha.reset(); // ✅ Resetear reCAPTCHA después del envío
      form
        .querySelectorAll('.is-valid, .is-invalid')
        .forEach((field) =>
          field.classList.remove('is-valid', 'is-invalid')
        );
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