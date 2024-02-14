let formErrorModal = {
    'name': 'Ошибки полей'
};

function showFormError(errors,) {
    let message = '';
    if (errors) {
        let count = errors.length;
        for (i = 0; i < count; i++) {
            message += errors[i]['message'] + '<br>';
        }
    }
    addModal(
        modalClasses.request,
        addModalRequestContent(formErrorModal.name, message),
    );
}

function sendFormAjax(formItem, fromName, successFunction, beforeSuccessFunction = function (currentParams) {
}, formReset = true) {
    let currentParams = new FormData(formItem);
    if (currentParams) {
        currentParams.append('form', fromName);
        currentParams.append('pageTitle', $('title').text());
        currentParams.append('pageUrl', url.href);
        beforeSuccessFunction(currentParams);
        let request = BX.ajax.runComponentAction('custom:forms', 'ajax', {
            mode: 'class',
            data: currentParams
        });
        request.then(function (response) {
            if (response.status === 'success') {
                if (response.data.customData.errors) {
                    showFormError(errors);
                } else {
                    successFunction(response.data);
                    if (formReset) {
                        formItem.reset();
                    }
                }
            }
        }, function (response) {
            showFormError(response.errors);
        })
    }
}

let accountPage = '/account/';
