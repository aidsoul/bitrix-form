
/**
 * @author AidSoul <work-aidsoul@outlook.com>
 */


class ComponentAjax {

    /**
     * 
     * @param {*} component 
     * @param {*} mode 
     * @param {*} method 
     * @param {*} params 
     */
    constructor(component, mode) {
        this.component = component;
        this.mode = mode;
        this.method = '';
        this.params = '';
        this.successFunction = function (response) { },
        this.errorFunction = function (error = { code, message, customData }) {},
        this.beforeErrorFunction = function () { }
    }

    /**
     * 
     * @param {*} params 
     */
    setParams(params) {
        this.params = params;
    }

    /**
     * ajax | class
     * @param {*} mode 
     */
    setMode(mode) {
        this.mode = mode;
    }

    successAction(successFunction = function (response) { }) {
        this.successFunction = successFunction;
    }

    errorsAction(response) {
        let errors = response.errors;
        if (errors) {
            let count = errors.length;
            let i = 0;
            for (; i < count; i++) {
                this.errorFunction(errors[i]);
            }
        }
    }

    /**
     * execute
     * 
     */
    execute() {
        let currentClass = this;
        let request = BX.ajax.runComponentAction(this.component, this.method, {
            mode: this.mode,
            data: this.params
        });
        request.then(function (response) {
            currentClass.successFunction(response);
        }, function (response) {
            currentClass.beforeErrorFunction();
            currentClass.errorsAction(response);
        });
    }
}

class FormComponentAjax extends ComponentAjax {
    constructor() {
        super('custom:forms', 'class');
        this.method = 'ajax';
        this.formData = '';
        this.formItem = '';
    }

    /**
     * 
     * @param {*} params 
     * @param {*} form 
     */
    setParams(params, form) {
        this.params = params;
        this.params.form = form;
    }

    /**
     * 
     * @param {*} form 
     * @param {*} formName 
     */
    setFormData(formItem, formName) {
        let formData = new FormData(formItem);
        if (formData) {
            formData.append('form', formName);
            this.params = formData;
            this.formData = formData;
            this.formItem = formItem;
        }
        console.log(formData.getAll('photos[]'), formData);
    }

    successClassAction() {
    }

}


