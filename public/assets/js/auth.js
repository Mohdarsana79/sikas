// Auth JavaScript - Handle Login and Register Forms for Bootstrap
class AuthHandler {
    constructor() {
        this.init();
    }

    init() {
        this.handleLoginForm();
        this.handleRegisterForm();
        this.handlePasswordToggle();
        this.handleFormValidation();
        this.enhanceFormAnimations();
        this.handleFlashMessages();
    }

    handleLoginForm() {
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
                this.handleFormSubmit(e, loginForm, 'login');
            });
        }
    }

    handleRegisterForm() {
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => {
                this.handleFormSubmit(e, registerForm, 'register');
            });
        }
    }

    handleFormSubmit(e, form, type) {
        e.preventDefault();
        
        // Clear previous errors
        this.clearErrors(form);
        
        // Validate form
        if (this.validateForm(form, type)) {
            this.showLoading(form, true);
            form.submit();
        }
    }

    validateForm(form, type) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required]');

        inputs.forEach(input => {
            if (!input.value.trim()) {
                this.showError(input, 'Field ini wajib diisi.');
                isValid = false;
            } else {
                if (!this.validateField(input, type)) {
                    isValid = false;
                }
            }
        });

        // Additional validation for register form
        if (type === 'register') {
            const password = form.querySelector('input[name="password"]');
            const passwordConfirmation = form.querySelector('input[name="password_confirmation"]');
            
            if (password && passwordConfirmation) {
                if (password.value !== passwordConfirmation.value) {
                    this.showError(passwordConfirmation, 'Konfirmasi password tidak sesuai.');
                    isValid = false;
                }
                
                if (password.value.length < 8) {
                    this.showError(password, 'Password minimal 8 karakter.');
                    isValid = false;
                }
            }
        }

        return isValid;
    }

    validateField(input, type) {
        const value = input.value.trim();
        
        switch(input.type) {
            case 'email':
                if (!this.isValidEmail(value)) {
                    this.showError(input, 'Format email tidak valid.');
                    return false;
                }
                break;
                
            case 'password':
                if (value.length < 8) {
                    this.showError(input, 'Password minimal 8 karakter.');
                    return false;
                }
                break;
                
            case 'text':
                if (input.name === 'name' && value.length < 2) {
                    this.showError(input, 'Nama minimal 2 karakter.');
                    return false;
                }
                break;
        }
        
        return true;
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    showError(input, message) {
        // Remove existing error
        this.removeError(input);
        
        // Add Bootstrap error classes
        input.classList.add('is-invalid');
        
        // Create error message element
        const errorElement = document.createElement('div');
        errorElement.className = 'invalid-feedback';
        errorElement.textContent = message;
        
        // Insert after input group
        const inputGroup = input.closest('.input-group');
        if (inputGroup) {
            inputGroup.appendChild(errorElement);
        } else {
            input.parentNode.appendChild(errorElement);
        }
    }

    removeError(input) {
        input.classList.remove('is-invalid');
        const existingError = input.parentNode.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
    }

    clearErrors(form) {
        const inputs = form.querySelectorAll('input');
        inputs.forEach(input => {
            this.removeError(input);
        });
    }

    handlePasswordToggle() {
        const toggleButtons = document.querySelectorAll('.password-toggle');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', () => {
                const input = button.closest('.input-group').querySelector('input');
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                
                // Change icon
                const icon = button.querySelector('i');
                if (type === 'text') {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });
    }

    enhanceFormAnimations() {
        const inputs = document.querySelectorAll('.form-control');
        
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', () => {
                if (!input.value) {
                    input.parentElement.classList.remove('focused');
                }
            });
            
            input.addEventListener('input', () => {
                if (input.value) {
                    input.classList.remove('is-invalid');
                    this.removeError(input);
                }
            });
        });
    }

    // handleFlashMessages
    handleFlashMessages() {
        // Handle success messages with SweetAlert
        if (document.querySelector('[data-success-message]')) {
            const successMessage = document.querySelector('[data-success-message]').getAttribute('data-success-message');
            if (successMessage) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: successMessage,
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        }

        // Handle error messages with SweetAlert
        if (document.querySelector('[data-error-message]')) {
            const errorMessage = document.querySelector('[data-error-message]').getAttribute('data-error-message');
            if (errorMessage) {
                Swal.fire({
                    icon: 'error',
                    title: 'Akses Ditolak!',
                    text: errorMessage,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#667eea'
                });
            }
        }

        // Handle Laravel session flash messages
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            const message = successAlert.textContent.trim();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
            successAlert.remove();
        }

        const errorAlert = document.querySelector('.alert-danger');
        if (errorAlert) {
            const message = errorAlert.textContent.trim();
            Swal.fire({
                icon: 'error',
                title: 'Akses Ditolak!',
                text: message,
                confirmButtonText: 'OK',
                confirmButtonColor: '#667eea'
            });
            errorAlert.remove();
        }
    }

    showLoading(form, show) {
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            if (show) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span class="visually-hidden" role="status">Loading...</span>';
            } else {
                submitButton.disabled = false;
                // Reset button text based on form type
                if (form.id === 'loginForm') {
                    submitButton.innerHTML = '<i class="bi bi-box-arrow-right me-2"></i>Sign In';
                } else {
                    submitButton.innerHTML = '<i class="bi bi-person-plus-fill me-2"></i>Create Account';
                }
            }
        }
    }

    // Method to show SweetAlert for validation errors
    static showValidationError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Validasi Error',
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#667eea'
        });
    }

    // Method to show SweetAlert for success
    static showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    new AuthHandler();
});

// Export for potential module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AuthHandler;
}