// resources/js/feedback-system.js

// Map to safely store callback functions referenced by UUID instead of inline code
const feedbackCallbacks = new Map();

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
    if (!container) return;

    // Generate unique IDs for this callback set
    const confirmId = crypto.randomUUID();
    const cancelId = crypto.randomUUID();

    // Store callbacks safely in a Map
    if (typeof onConfirm === 'function') {
      feedbackCallbacks.set(confirmId, onConfirm);
    }
    if (typeof onCancel === 'function') {
      feedbackCallbacks.set(cancelId, onCancel);
    }

    // Create modal container
    const modalOverlay = document.createElement('div');
    modalOverlay.className = 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center';
    modalOverlay.id = 'confirm-modal-' + confirmId;

    const modalBox = document.createElement('div');
    modalBox.className = 'bg-white rounded-xl shadow-lg w-full max-w-md p-6';

    // Create title - use textContent to safely escape HTML
    const titleElement = document.createElement('h3');
    titleElement.className = 'text-lg font-bold text-on-surface mb-2';
    titleElement.textContent = title;
    modalBox.appendChild(titleElement);

    // Create message - use textContent to safely escape HTML
    const messageElement = document.createElement('p');
    messageElement.className = 'text-on-surface-variant mb-6';
    messageElement.textContent = message;
    modalBox.appendChild(messageElement);

    // Create button container
    const buttonContainer = document.createElement('div');
    buttonContainer.className = 'flex gap-2';

    // Create cancel button
    const cancelButton = document.createElement('button');
    cancelButton.className = 'flex-1 py-2 border border-outline-variant rounded-lg text-sm';
    cancelButton.textContent = 'Cancelar';
    cancelButton.addEventListener('click', () => {
      if (feedbackCallbacks.has(cancelId)) {
        feedbackCallbacks.get(cancelId)();
        feedbackCallbacks.delete(cancelId);
      }
      modalOverlay.remove();
      if (feedbackCallbacks.has(confirmId)) {
        feedbackCallbacks.delete(confirmId);
      }
    });
    buttonContainer.appendChild(cancelButton);

    // Create confirm button
    const confirmButton = document.createElement('button');
    confirmButton.className = 'flex-1 bg-primary text-on-primary py-2 rounded-lg text-sm font-semibold';
    confirmButton.textContent = 'Confirmar';
    confirmButton.addEventListener('click', () => {
      if (feedbackCallbacks.has(confirmId)) {
        feedbackCallbacks.get(confirmId)();
        feedbackCallbacks.delete(confirmId);
      }
      modalOverlay.remove();
      if (feedbackCallbacks.has(cancelId)) {
        feedbackCallbacks.delete(cancelId);
      }
    });
    buttonContainer.appendChild(confirmButton);

    modalBox.appendChild(buttonContainer);
    modalOverlay.appendChild(modalBox);
    container.appendChild(modalOverlay);
  },

  alert(title, message) {
    const container = document.getElementById('feedback-modal-container');
    if (!container) return;

    // Create modal container
    const modalOverlay = document.createElement('div');
    modalOverlay.className = 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center';
    modalOverlay.id = 'alert-modal-' + crypto.randomUUID();

    const modalBox = document.createElement('div');
    modalBox.className = 'bg-white rounded-xl shadow-lg w-full max-w-md p-6';

    // Create title - use textContent to safely escape HTML
    const titleElement = document.createElement('h3');
    titleElement.className = 'text-lg font-bold text-on-surface mb-2';
    titleElement.textContent = title;
    modalBox.appendChild(titleElement);

    // Create message - use textContent to safely escape HTML
    const messageElement = document.createElement('p');
    messageElement.className = 'text-on-surface-variant mb-6';
    messageElement.textContent = message;
    modalBox.appendChild(messageElement);

    // Create OK button
    const okButton = document.createElement('button');
    okButton.className = 'w-full bg-primary text-on-primary py-2 rounded-lg text-sm font-semibold';
    okButton.textContent = 'Ok';
    okButton.addEventListener('click', () => {
      modalOverlay.remove();
    });
    modalBox.appendChild(okButton);

    modalOverlay.appendChild(modalBox);
    container.appendChild(modalOverlay);
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

    // Create toast container
    const toastElement = document.createElement('div');
    toastElement.className = `toast p-4 mb-3 border rounded-lg flex items-center gap-3 ${colors[type]}`;
    toastElement.style.animation = 'slideInUp 0.3s ease-out';

    // Create icon
    const iconSpan = document.createElement('span');
    iconSpan.className = 'material-symbols-outlined text-lg';
    iconSpan.textContent = icons[type];
    toastElement.appendChild(iconSpan);

    // Create message - use textContent to safely escape HTML
    const messageElement = document.createElement('p');
    messageElement.className = 'text-sm';
    messageElement.textContent = message;
    toastElement.appendChild(messageElement);

    // Create close button
    const closeButton = document.createElement('button');
    closeButton.className = 'ml-auto opacity-70 hover:opacity-100';
    closeButton.addEventListener('click', () => {
      toastElement.remove();
    });

    const closeIcon = document.createElement('span');
    closeIcon.className = 'material-symbols-outlined text-lg';
    closeIcon.textContent = 'close';
    closeButton.appendChild(closeIcon);
    toastElement.appendChild(closeButton);

    container.appendChild(toastElement);

    setTimeout(() => {
      if (toastElement.parentElement) {
        toastElement.style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => {
          if (toastElement.parentElement) {
            toastElement.remove();
          }
        }, 300);
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
