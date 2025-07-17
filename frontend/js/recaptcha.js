// js/recaptcha.js
import { CONFIG } from '../constants/config.js';

export function initRecaptcha() {
  const container = document.getElementById('recaptcha-container');
  if (!container) return;

  // Si ya se cargó, evitamos duplicar
  if (typeof grecaptcha !== 'undefined') {
    grecaptcha.render(container, {
      sitekey: CONFIG.RECAPTCHA_SITE_KEY,
    });
  } else {
    console.warn('Google reCAPTCHA aún no está disponible.');
  }
}