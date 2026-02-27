/**
 * Forms Component JavaScript
 * @author AidSoul <work-aidsoul@outlook.com>
 */

/**
 * Base AJAX Component class for Bitrix
 */
class ComponentAjax {
    /**
     * @param {string} component - Component name (e.g., 'aidsoul:forms')
     * @param {string} mode - AJAX mode: 'class' or 'function'
     */
    constructor(component, mode = 'class') {
        this.component = component;
        this.mode = mode;
        this.method = '';
        this.params = {};
        this.successFunction = null;
        this.errorFunction = null;
        this.beforeSendFunction = null;
        this.completeFunction = null;
    }

    /**
     * Set parameters for AJAX request
     * @param {Object} params - Parameters to send
     */
    setParams(params) {
        this.params = params;
        return this;
    }

    /**
     * Set AJAX mode
     * @param {string} mode - 'class' or 'function'
     */
    setMode(mode) {
        this.mode = mode;
        return this;
    }

    /**
     * Set success callback
     * @param {Function} successFunction - Callback function
     */
    successAction(successFunction) {
        this.successFunction = successFunction;
        return this;
    }

    /**
     * Set error callback
     * @param {Function} errorFunction - Callback function
     */
    errorAction(errorFunction) {
        this.errorFunction = errorFunction;
        return this;
    }

    /**
     * Set before send callback
     * @param {Function} beforeSendFunction - Callback function
     */
    beforeSendAction(beforeSendFunction) {
        this.beforeSendFunction = beforeSendFunction;
        return this;
    }

    /**
     * Set complete callback
     * @param {Function} completeFunction - Callback function
     */
    completeAction(completeFunction) {
        this.completeFunction = completeFunction;
        return this;
    }

    /**
     * Process errors from response
     * @param {Object} response - Error response
     */
    errorsAction(response) {
        let errors = response.errors || [];
        if (errors.length > 0) {
            errors.forEach(error => {
                if (this.errorFunction) {
                    this.errorFunction(error);
                }
            });
        }
    }

    /**
     * Execute AJAX request
     */
    execute() {
        let currentClass = this;
        
        if (this.beforeSendFunction) {
            this.beforeSendFunction();
        }

        let request = BX.ajax.runComponentAction(this.component, this.method, {
            mode: this.mode,
            data: this.params
        });

        request.then(
            function(response) {
                if (currentClass.successFunction) {
                    currentClass.successFunction(response.data, response);
                }
                if (currentClass.completeFunction) {
                    currentClass.completeFunction(response);
                }
            },
            function(response) {
                currentClass.errorsAction(response);
                if (currentClass.completeFunction) {
                    currentClass.completeFunction(response);
                }
            }
        );

        return request;
    }
}

/**
 * Form AJAX handler class
 */
class FormComponentAjax extends ComponentAjax {
    /**
     * @param {string} formSelector - Form ID or selector
     */
    constructor(formSelector) {
        super('aidsoul:forms', 'class');
        this.method = 'ajax';
        this.formElement = null;
        
        // Get form element
        if (typeof formSelector === 'string') {
            this.formElement = document.querySelector(formSelector);
            if (!this.formElement && formSelector.startsWith('#')) {
                this.formElement = document.getElementById(formSelector.substring(1));
            }
        } else if (formSelector instanceof HTMLElement) {
            this.formElement = formSelector;
        }

        if (this.formElement) {
            this.init();
        }
    }

    /**
     * Initialize form event listeners
     */
    init() {
        let form = this.formElement;
        let formName = form.dataset.formName || 'default';
        let isAjax = form.dataset.ajax !== 'false';

        if (isAjax) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitForm(form, formName);
            });
        }
    }

    /**
     * Submit form via AJAX
     * @param {HTMLFormElement} form - Form element
     * @param {string} formName - Form name identifier
     */
    submitForm(form, formName) {
        let formData = new FormData(form);
        formData.append('form', formName);
        
        // Clear previous messages
        this.clearMessages(form);

        // Show loading state
        this.setLoading(form, true);

        // Prepare params
        this.params = this.prepareFormData(formData);

        // Set default callbacks
        this.successAction((data, response) => {
            this.setLoading(form, false);
            this.handleSuccess(form, data, response);
        });

        this.errorAction((error) => {
            this.setLoading(form, false);
            this.handleError(form, error);
        });

        this.execute();
    }

    /**
     * Prepare FormData for request
     * @param {FormData} formData - Raw form data
     * @returns {Object} - Processed data
     */
    prepareFormData(formData) {
        let params = {};
        
        for (let [key, value] of formData.entries()) {
            if (key.endsWith('[]')) {
                let baseKey = key.slice(0, -2);
                if (!params[baseKey]) {
                    params[baseKey] = [];
                }
                params[baseKey].push(value);
            } else {
                params[key] = value;
            }
        }
        
        return params;
    }

    /**
     * Handle successful response
     * @param {HTMLFormElement} form - Form element
     * @param {Object} data - Response data
     * @param {Object} response - Full response
     */
    handleSuccess(form, data, response) {
        let messageBlock = form.querySelector('.form-success');
        
        if (data.message) {
            this.showMessage(form, 'success', data.message);
        }

        // Hide form if needed
        if (data.hideForm) {
            form.style.display = 'none';
        }

        // Redirect if needed
        if (data.redirect) {
            setTimeout(() => {
                window.location.href = data.redirect;
            }, data.redirectDelay || 1000);
        }

        // Reset form
        if (data.resetForm !== false) {
            form.reset();
        }

        // Trigger custom event
        form.dispatchEvent(new CustomEvent('form-success', { 
            detail: { data, response }
        }));

        // Run custom success callback if defined
        if (this.customSuccessCallback) {
            this.customSuccessCallback(data, response);
        }
    }

    /**
     * Handle error response
     * @param {HTMLFormElement} form - Form element
     * @param {Object} error - Error object
     */
    handleError(form, error) {
        // Show field-specific errors
        if (error.customData && error.customData.fields) {
            Object.entries(error.customData.fields).forEach(([fieldName, fieldError]) => {
                let errorElement = form.querySelector(`#error_${fieldName}`);
                if (errorElement) {
                    errorElement.textContent = fieldError;
                    errorElement.style.display = 'block';
                }
                
                let fieldElement = form.querySelector(`[name="${fieldName}"]`);
                if (fieldElement) {
                    fieldElement.classList.add('field-error');
                }
            });
        }

        // Show general error message
        let message = error.message || 'Произошла ошибка. Попробуйте позже.';
        this.showMessage(form, 'error', message);

        // Trigger custom event
        form.dispatchEvent(new CustomEvent('form-error', { 
            detail: { error }
        }));
    }

    /**
     * Show message (success/error)
     * @param {HTMLFormElement} form - Form element
     * @param {string} type - Message type: 'success' or 'error'
     * @param {string} message - Message text
     */
    showMessage(form, type, message) {
        let messageBlock = form.querySelector(`.form-${type}`);
        if (messageBlock) {
            messageBlock.textContent = message;
            messageBlock.style.display = 'block';
            
            // Auto-hide after delay for success messages
            if (type === 'success') {
                setTimeout(() => {
                    messageBlock.style.display = 'none';
                }, 5000);
            }
        }
    }

    /**
     * Clear all messages
     * @param {HTMLFormElement} form - Form element
     */
    clearMessages(form) {
        let messages = form.querySelectorAll('.form-success, .form-error');
        messages.forEach(msg => {
            msg.textContent = '';
            msg.style.display = 'none';
        });

        let errors = form.querySelectorAll('.field-error');
        errors.forEach(err => {
            err.textContent = '';
            err.style.display = 'none';
        });

        let fields = form.querySelectorAll('.field-error-input');
        fields.forEach(field => {
            field.classList.remove('field-error-input');
        });
    }

    /**
     * Set loading state
     * @param {HTMLFormElement} form - Form element
     * @param {boolean} loading - Loading state
     */
    setLoading(form, loading) {
        let submitBtn = form.querySelector('[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = loading;
            submitBtn.classList.toggle('loading', loading);
            
            if (loading) {
                submitBtn.dataset.originalText = submitBtn.textContent;
                submitBtn.textContent = 'Отправка...';
            } else {
                submitBtn.textContent = submitBtn.dataset.originalText || 'Отправить';
            }
        }
    }

    /**
     * Set custom success callback
     * @param {Function} callback - Callback function
     */
    onSuccess(callback) {
        this.customSuccessCallback = callback;
        return this;
    }

    /**
     * Set custom error callback
     * @param {Function} callback - Callback function
     */
    onError(callback) {
        this.errorFunction = callback;
        return this;
    }
}

/**
 * Initialize all forms on page
 */
document.addEventListener('DOMContentLoaded', function() {
    // Auto-initialize forms with data-ajax="true"
    let ajaxForms = document.querySelectorAll('form[data-ajax="true"]');
    ajaxForms.forEach(form => {
        if (!form.dataset.initialized) {
            form.dataset.initialized = 'true';
            new FormComponentAjax(form);
        }
    });
});

// Export for use in other scripts
if (typeof window !== 'undefined') {
    window.FormComponentAjax = FormComponentAjax;
    window.ComponentAjax = ComponentAjax;
}
