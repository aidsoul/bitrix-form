document.addEventListener('DOMContentLoaded', function () {
    document.querySelector('#form-login').addEventListener('submit', function (e) {
        e.preventDefault();
        sendFormAjax(this, 'login', function (data) {
            window.location = data.url;
        })
    });
})