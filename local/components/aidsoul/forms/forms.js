/**
 * Forms Component JavaScript
 * @author AidSoul <work-aidsoul@outlook.com>
 */

/**
 * Vanilla JavaScript AJAX class (without BX.ajax)
 */
class Ajax {
    /**
     * @param {Object} options - AJAX options
     */
    constructor(options = {}) {
        this.url = options.url || '';
        this.method = options.method || 'POST';
        this.headers = options.headers || {};
        this.body = options.body || null;
        this.dataType = options.dataType || 'json';
        this.timeout = options.timeout || 30000;
        this.withCredentials = options.withCredentials || false;
        
        // Callbacks
        this.beforeSend = options.beforeSend || null;
        this.success = options.success || null;
        this.error = options.error || null;
        this.complete = options.complete || null;
        this.progress = options.progress || null;
        
        // Request object for abort
        this.request = null;
        this.aborted = false;
    }
    
    /**
     * Set request URL
     * @param {string} url - Request URL
     */
    setUrl(url) {
        this.url = url;
        return this;
    }
    
    /**
     * Set request method
     * @param {string} method - HTTP method
     */
    setMethod(method) {
        this.method = method.toUpperCase();
        return this;
    }
    
    /**
     * Set request headers
     * @param {Object} headers - Headers object
     */
    setHeaders(headers) {
        this.headers = { ...this.headers, ...headers };
        return this;
    }
    
    /**
     * Set request body
     * @param {Object|FormData|string} body - Request body
     */
    setData(body) {
        this.body = body;
        return this;
    }
    
    /**
     * Set data type
     * @param {string} type - Data type: 'json', 'text', 'formdata'
     */
    setDataType(type) {
        this.dataType = type;
        return this;
    }
    
    /**
     * Set timeout
     * @param {number} timeout - Timeout in milliseconds
     */
    setTimeout(timeout) {
        this.timeout = timeout;
        return this;
    }
    
    /**
     * Set before send callback
     * @param {Function} callback - Callback function
     */
    beforeSend(callback) {
        this.beforeSend = callback;
        return this;
    }
    
    /**
     * Set success callback
     * @param {Function} callback - Callback function
     */
    success(callback) {
        this.success = callback;
        return this;
    }
    
    /**
     * Set error callback
     * @param {Function} callback - Callback function
     */
    error(callback) {
        this.error = callback;
        return this;
    }
    
    /**
     * Set complete callback
     * @param {Function} callback - Callback function
     */
    complete(callback) {
        this.complete = callback;
        return this;
    }
    
    /**
     * Set progress callback
     * @param {Function} callback - Callback function
     */
    progress(callback) {
        this.progress = callback;
        return this;
    }
    
    /**
     * Build URL with query params
     * @param {Object} params - Query parameters
     * @returns {string} - URL with query string
     */
    buildUrl(params) {
        if (!params || Object.keys(params).length === 0) {
            return this.url;
        }
        
        const url = new URL(this.url, window.location.origin);
        Object.keys(params).forEach(key => {
            const value = params[key];
            if (Array.isArray(value)) {
                value.forEach(v => url.searchParams.append(key + '[]', v));
            } else {
                url.searchParams.append(key, value);
            }
        });
        
        return url.toString();
    }
    
    /**
     * Prepare headers for fetch
     * @returns {Headers} - Prepared headers
     */
    prepareHeaders() {
        const headers = new Headers();
        
        Object.keys(this.headers).forEach(key => {
            headers.append(key, this.headers[key]);
        });
        
        // Set default content-type for JSON
        if (this.dataType === 'json' && !headers.has('Content-Type')) {
            headers.append('Content-Type', 'application/json');
        }
        
        // Set default content-type for form data
        if (this.body instanceof FormData && !headers.has('Content-Type')) {
            // Let browser set Content-Type with boundary for FormData
        }
        
        return headers;
    }
    
    /**
     * Prepare body for fetch
     * @returns {string|FormData|null} - Prepared body
     */
    prepareBody() {
        if (this.body instanceof FormData) {
            return this.body;
        }
        
        if (this.dataType === 'json' && typeof this.body === 'object') {
            return JSON.stringify(this.body);
        }
        
        return this.body;
    }
    
    /**
     * Execute the AJAX request
     */
    execute() {
        this.aborted = false;
        
        // Call beforeSend
        if (this.beforeSend) {
            this.beforeSend(this);
        }
        
        const fetchOptions = {
            method: this.method,
            headers: this.prepareHeaders(),
            credentials: this.withCredentials ? 'include' : 'same-origin'
        };
        
        // Add body for non-GET requests
        if (this.method !== 'GET' && this.body) {
            fetchOptions.body = this.prepareBody();
        }
        
        // Use timeout with Promise.race
        const timeoutPromise = new Promise((_, reject) => {
            setTimeout(() => {
                if (!this.aborted) {
                    reject(new Error('Request timeout'));
                }
            }, this.timeout);
        });
        
        const fetchPromise = fetch(this.url, fetchOptions);
        
        this.request = Promise.race([fetchPromise, timeoutPromise])
            .then(response => {
                if (this.aborted) {
                    return Promise.reject(new Error('Request aborted'));
                }
                
                // Check response status
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Parse response based on dataType
                switch (this.dataType) {
                    case 'json':
                        return response.json();
                    case 'text':
                        return response.text();
                    case 'formdata':
                        return response.formData();
                    default:
                        return response.json();
                }
            })
            .then(data => {
                if (this.success) {
                    this.success(data);
                }
                
                if (this.complete) {
                    this.complete(data);
                }
                
                return data;
            })
            .catch(error => {
                if (this.aborted) {
                    return;
                }
                
                if (this.error) {
                    this.error(error);
                }
                
                if (this.complete) {
                    this.complete(null, error);
                }
                
                throw error;
            });
        
        return this.request;
    }
    
    /**
     * Abort the current request
     */
    abort() {
        this.aborted = true;
        // Note: fetch cannot be directly aborted in all browsers
        // but we can prevent the handlers from executing
    }
    
    /**
     * Static helper for GET request
     * @param {string} url - Request URL
     * @param {Object} params - Query parameters
     * @param {Object} options - Additional options
     */
    static get(url, params = {}, options = {}) {
        const fullUrl = new URL(url, window.location.origin).toString();
        const ajax = new Ajax({
            url: fullUrl,
            method: 'GET',
            ...options
        });
        ajax.url = ajax.buildUrl(params);
        return ajax.execute();
    }
    
    /**
     * Static helper for POST request
     * @param {string} url - Request URL
     * @param {Object} data - Request body
     * @param {Object} options - Additional options
     */
    static post(url, data = {}, options = {}) {
        return new Ajax({
            url: url,
            method: 'POST',
            body: data,
            dataType: 'json',
            ...options
        }).execute();
    }
    
    /**
     * Static helper for PUT request
     * @param {string} url - Request URL
     * @param {Object} data - Request body
     * @param {Object} options - Additional options
     */
    static put(url, data = {}, options = {}) {
        return new Ajax({
            url: url,
            method: 'PUT',
            body: data,
            dataType: 'json',
            ...options
        }).execute();
    }
    
    /**
     * Static helper for DELETE request
     * @param {string} url - Request URL
     * @param {Object} options - Additional options
     */
    static delete(url, options = {}) {
        return new Ajax({
            url: url,
            method: 'DELETE',
            ...options
        }).execute();
    }
}

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
     * Get AJAX URL for component action
     * @returns {string} - AJAX URL
     */
    getAjaxUrl() {
        const path = this.mode === 'class' 
            ? `/bitrix/components/${this.component}/ajax.php`
            : `/bitrix/components/${this.component}/exec.php`;
        
        return path;
    }

    /**
     * Execute AJAX request using vanilla JavaScript (without BX.ajax)
     */
    executeVanilla() {
        let currentClass = this;
        const ajaxUrl = this.getAjaxUrl();
        
        if (this.beforeSendFunction) {
            this.beforeSendFunction();
        }

        return new Ajax({
            url: ajaxUrl,
            method: 'POST',
            data: {
                mode: this.mode,
                action: this.method,
                ...this.params
            },
            dataType: 'json',
            beforeSend: () => {
                // beforeSend already called
            },
            success: (response) => {
                // Check for Bitrix error response
                if (response.errors && response.errors.length > 0) {
                    currentClass.errorsAction(response);
                    if (currentClass.errorFunction) {
                        currentClass.errorFunction(response.errors[0]);
                    }
                } else if (currentClass.successFunction) {
                    currentClass.successFunction(response.data, response);
                }
                
                if (currentClass.completeFunction) {
                    currentClass.completeFunction(response);
                }
            },
            error: (error) => {
                if (currentClass.errorFunction) {
                    currentClass.errorFunction({
                        message: error.message || 'Network error',
                        code: 'NETWORK_ERROR'
                    });
                }
                
                if (currentClass.completeFunction) {
                    currentClass.completeFunction(null, error);
                }
            }
        }).execute();
    }

    /**
     * Execute AJAX request using BX.ajax (original method)
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
    window.Ajax = Ajax;
}
