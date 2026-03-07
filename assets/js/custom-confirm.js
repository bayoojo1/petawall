class CustomConfirm {
    constructor() {
        this.confirmCallback = null;
        this.cancelCallback = null;
        this.currentForm = null;
        this.currentButton = null;
        this.pendingSubmission = false;

        this.modal = document.getElementById('customConfirmModal');
        this.messageEl = document.getElementById('confirmModalMessage');
        this.confirmBtn = this.modal.querySelector('.confirm-btn-confirm');
        this.cancelBtn = this.modal.querySelector('.confirm-btn-cancel');
        this.closeBtn = this.modal.querySelector('.confirm-modal-close');
        this.content = this.modal.querySelector('.confirm-modal-content');

        this.init();
    }

    init() {
        setTimeout(() => {
            this.setupConfirmButtons();
        }, 100);

        this.setupModalEvents();
    }

    /* ================================
       SETUP BUTTONS
    ================================= */

    setupConfirmButtons() {
        document.querySelectorAll(
            'button[data-confirm-message], input[type="submit"][data-confirm-message]'
        ).forEach(button => {
            this.setupButtonConfirm(button);
        });

        document.querySelectorAll('form[data-confirm-message]').forEach(form => {
            this.setupFormConfirm(form);
        });

        this.replaceInlineConfirms();
    }

    setupButtonConfirm(button) {
        if (button.closest('form')) {
            const form = button.closest('form');

            if (
                button.type === 'submit' ||
                button.getAttribute('type') === 'submit'
            ) {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    e.stopPropagation();

                    this.currentForm = form;
                    this.currentButton = button;

                    const message =
                        button.dataset.confirmMessage ||
                        form.dataset.confirmMessage ||
                        'Are you sure you want to proceed?';

                    const type =
                        button.dataset.confirmType ||
                        form.dataset.confirmType ||
                        'primary';

                    this.showConfirm(message, type);
                });
            }
        } else {
            button.addEventListener('click', e => {
                e.preventDefault();

                const message =
                    button.dataset.confirmMessage ||
                    'Are you sure you want to proceed?';

                const type = button.dataset.confirmType || 'primary';
                const actionUrl = button.dataset.actionUrl;
                const method = button.dataset.method || 'GET';

                this.showConfirm(message, type, () => {
                    if (actionUrl) {
                        if (method === 'POST') {
                            this.submitPostRequest(actionUrl, button.dataset);
                        } else {
                            window.location.href = actionUrl;
                        }
                    }
                });
            });
        }
    }

    setupFormConfirm(form) {
        form.addEventListener('submit', e => {
            if (!this.pendingSubmission) {
                e.preventDefault();

                this.currentForm = form;
                this.currentButton =
                    form.querySelector('button[type="submit"]');

                const message =
                    form.dataset.confirmMessage ||
                    'Are you sure you want to proceed?';

                const type = form.dataset.confirmType || 'primary';

                this.showConfirm(message, type);
            }
        });
    }

    /* ================================
       SHOW MODAL
    ================================= */

    showConfirm(message, type = 'primary', confirmCallback = null, cancelCallback = null) {
        this.confirmCallback = confirmCallback;
        this.cancelCallback = cancelCallback;

        this.messageEl.textContent = message;

        // Reset styling
        this.content.classList.remove('danger', 'warning', 'success');
        this.confirmBtn.className = 'confirm-btn confirm-btn-confirm';

        // Apply type styling
        if (type === 'danger') {
            this.content.classList.add('danger');
            this.confirmBtn.classList.add('confirm-btn-danger');
        } else if (type === 'warning') {
            this.content.classList.add('warning');
            this.confirmBtn.classList.add('confirm-btn-warning');
        } else if (type === 'success') {
            this.content.classList.add('success');
            this.confirmBtn.classList.add('confirm-btn-success');
        } else {
            this.confirmBtn.classList.add('confirm-btn-primary');
        }

        this.modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    hideConfirm() {
        this.modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }

    /* ================================
       MODAL EVENTS
    ================================= */

    setupModalEvents() {
        this.confirmBtn.addEventListener('click', () => {
            if (this.confirmCallback) {
                this.confirmCallback();
            } else if (this.currentForm) {
                this.submitForm();
            }

            this.reset();
            this.hideConfirm();
        });

        this.cancelBtn.addEventListener('click', () => {
            if (this.cancelCallback) {
                this.cancelCallback();
            }

            this.reset();
            this.hideConfirm();
        });

        this.closeBtn.addEventListener('click', () => {
            this.reset();
            this.hideConfirm();
        });

        this.modal.addEventListener('click', e => {
            if (e.target === this.modal) {
                this.reset();
                this.hideConfirm();
            }
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && this.modal.classList.contains('show')) {
                this.reset();
                this.hideConfirm();
            }
        });
    }

    /* ================================
       FORM SUBMISSION
    ================================= */

    submitForm() {
        if (!this.currentForm) return;

        this.pendingSubmission = true;

        if (this.currentButton) {
            const originalHTML = this.currentButton.innerHTML;

            this.currentButton.innerHTML =
                '<i class="fas fa-spinner fa-spin"></i> Processing...';
            this.currentButton.disabled = true;

            setTimeout(() => {
                if (this.currentButton) {
                    this.currentButton.innerHTML = originalHTML;
                    this.currentButton.disabled = false;
                }
            }, 2000);
        }

        this.currentForm.submit();
    }

    submitPostRequest(url, data = {}) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.style.display = 'none';

        const csrfToken =
            document.querySelector('meta[name="csrf-token"]')?.content;

        if (csrfToken) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_token';
            input.value = csrfToken;
            form.appendChild(input);
        }

        Object.keys(data).forEach(key => {
            if (
                key !== 'confirmMessage' &&
                key !== 'confirmType' &&
                key !== 'actionUrl' &&
                key !== 'method'
            ) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = data[key];
                form.appendChild(input);
            }
        });

        document.body.appendChild(form);
        form.submit();
    }

    reset() {
        this.confirmCallback = null;
        this.cancelCallback = null;
        this.currentForm = null;
        this.currentButton = null;
        this.pendingSubmission = false;
    }

    replaceInlineConfirms() {
        document.querySelectorAll('[onclick*="confirm("]').forEach(element => {
            const onclick = element.getAttribute('onclick');
            const match = onclick.match(/confirm\(['"]([^'"]+)['"]\)/);

            if (match) {
                const message = match[1];
                element.removeAttribute('onclick');
                element.dataset.confirmMessage = message;

                if (
                    element.tagName === 'BUTTON' ||
                    element.tagName === 'INPUT'
                ) {
                    this.setupButtonConfirm(element);
                }
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.customConfirm = new CustomConfirm();
});