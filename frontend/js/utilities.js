export function showAlert(message, type = 'info') {
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