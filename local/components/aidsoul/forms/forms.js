
function sendFormAjax(formItem, fromName, successFunction) {
    let currentParams = new FormData(formItem);
    // let currentUrl = new URL(window.location.href);
    if (currentParams) {
        currentParams.append('form', fromName);
        currentParams.append('pageTitle', $('title').text());
        let request = BX.ajax.runComponentAction('custom:forms', 'ajax', {
            mode: 'class',
            data: currentParams
        });
        request.then(function (response) {
            if (response.status === 'success') {
                successFunction(response.data);
            }
        }, function (response) {
            let message = '';
            if (response.errors) {
                let count = response.errors.length;
                for (i = 0; i < count; i++) {
                    message += response.errors[i]['message'] + ' \n ';
                }
            }
            alert(message);
        })
    }
}

