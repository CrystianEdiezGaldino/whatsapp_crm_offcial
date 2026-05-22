// resources/js/feedback-system.js

window.Feedback = {
  success(message, duration = 3000) {
    this.toast(message, 'success', duration);
  },

  error(message, duration = 5000) {
    this.toast(message, 'error', duration);
  },

  warning(message, duration = 4000) {
    this.toast(message, 'warning', duration);
  },

  info(message, duration = 3000) {
    this.toast(message, 'info', duration);
  },

  confirm(title, message, onConfirm, onCancel) {
    const container = document.getElementById('feedback-modal-container');
    const html = `
      <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" id="confirm-modal">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
          <h3 class="text-lg font-bold text-on-surface mb-2">${title}</h3>
          <p class="text-on-surface-variant mb-6">${message}</p>
          <div class="flex gap-2">
            <button class="flex-1 py-2 border border-outline-variant rounded-lg text-sm" onclick="document.getElementById('confirm-modal').remove(); ${onCancel || ''}">Cancelar</button>
            <button class="flex-1 bg-primary text-on-primary py-2 rounded-lg text-sm font-semibold" onclick="document.getElementById('confirm-modal').remove(); ${onConfirm}">Confirmar</button>
          </div>
        </div>
      </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
  },

  alert(title, message) {
    const container = document.getElementById('feedback-modal-container');
    const html = `
      <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" id="alert-modal">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
          <h3 class="text-lg font-bold text-on-surface mb-2">${title}</h3>
          <p class="text-on-surface-variant mb-6">${message}</p>
          <button class="w-full bg-primary text-on-primary py-2 rounded-lg text-sm font-semibold" onclick="document.getElementById('alert-modal').remove()">Ok</button>
        </div>
      </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
  },

  toast(message, type = 'info', duration = 3000) {
    const container = document.getElementById('feedback-toast-container');
    if (!container) return;

    const icons = {
      success: 'check_circle',
      error: 'error',
      warning: 'warning',
      info: 'info'
    };

    const colors = {
      success: 'bg-green-50 border-green-200 text-green-800',
      error: 'bg-red-50 border-red-200 text-red-800',
      warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
      info: 'bg-blue-50 border-blue-200 text-blue-800'
    };

    const html = `
      <div class="toast p-4 mb-3 border rounded-lg flex items-center gap-3 ${colors[type]}" style="animation: slideInUp 0.3s ease-out;">
        <span class="material-symbols-outlined text-lg">${icons[type]}</span>
        <p class="text-sm">${message}</p>
        <button onclick="this.parentElement.remove()" class="ml-auto opacity-70 hover:opacity-100">
          <span class="material-symbols-outlined text-lg">close</span>
        </button>
      </div>
    `;

    container.insertAdjacentHTML('beforeend', html);

    setTimeout(() => {
      const toasts = container.querySelectorAll('.toast');
      if (toasts.length > 0) {
        toasts[toasts.length - 1].style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => toasts[toasts.length - 1].remove(), 300);
      }
    }, duration);
  }
};

if (typeof document !== 'undefined') {
  const style = document.createElement('style');
  style.textContent = `
    @keyframes slideInUp {
      from {
        transform: translateY(100%);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }
    @keyframes fadeOut {
      from {
        opacity: 1;
      }
      to {
        opacity: 0;
      }
    }
  `;
  document.head.appendChild(style);
}
