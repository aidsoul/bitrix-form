# JavaScript API

## Table of Contents

1. [Ajax Class](#ajax-class) - Vanilla JavaScript AJAX without BX.ajax
2. [ComponentAjax Class](#componentajax-class) - Component AJAX handler
3. [FormComponentAjax Class](#formcomponentajax-class) - Form AJAX handler

---

## Ajax Class

Vanilla JavaScript AJAX class that works without BX.ajax. Uses native fetch API.

### Constructor

```javascript
new Ajax(options)
```

#### Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `url` | string | `''` | Request URL |
| `method` | string | `'POST'` | HTTP method (GET, POST, PUT, DELETE) |
| `headers` | object | `{}` | Request headers |
| `body` | object/FormData | `null` | Request body |
| `dataType` | string | `'json'` | Response data type (json, text, formdata) |
| `timeout` | number | `30000` | Request timeout in milliseconds |
| `withCredentials` | boolean | `false` | Include cookies |
| `beforeSend` | function | `null` | Callback before request |
| `success` | function | `null` | Callback on success |
| `error` | function | `null` | Callback on error |
| `complete` | function | `null` | Callback on complete |
| `progress` | function | `null` | Progress callback |

### Methods

#### setUrl(url)

Set request URL.

```javascript
ajax.setUrl('/api/endpoint');
```

#### setMethod(method)

Set HTTP method.

```javascript
ajax.setMethod('POST');
```

#### setHeaders(headers)

Set request headers.

```javascript
ajax.setHeaders({
    'X-Custom-Header': 'value',
    'Authorization': 'Bearer token'
});
```

#### setData(body)

Set request body.

```javascript
ajax.setData({ name: 'John', email: 'john@example.com' });
```

#### setDataType(type)

Set data type.

```javascript
ajax.setDataType('json'); // json, text, formdata
```

#### setTimeout(timeout)

Set timeout in milliseconds.

```javascript
ajax.setTimeout(30000);
```

#### beforeSend(callback)

Set before send callback.

```javascript
ajax.beforeSend(function() {
    console.log('Request starting...');
});
```

#### success(callback)

Set success callback.

```javascript
ajax.success(function(data) {
    console.log('Success:', data);
});
```

#### error(callback)

Set error callback.

```javascript
ajax.error(function(error) {
    console.log('Error:', error.message);
});
```

#### complete(callback)

Set complete callback.

```javascript
ajax.complete(function() {
    console.log('Request completed');
});
```

#### execute()

Execute the AJAX request.

```javascript
ajax.execute();
```

#### abort()

Abort the current request.

```javascript
ajax.abort();
```

### Static Methods

#### Ajax.get(url, params, options)

Execute GET request.

```javascript
Ajax.get('/api/data', { page: 1 }, {
    success: function(data) { console.log(data); }
});
```

#### Ajax.post(url, data, options)

Execute POST request.

```javascript
Ajax.post('/api/submit', { name: 'John' })
    .then(function(data) { console.log(data); });
```

#### Ajax.put(url, data, options)

Execute PUT request.

```javascript
Ajax.put('/api/update/1', { name: 'John Updated' });
```

#### Ajax.delete(url, options)

Execute DELETE request.

```javascript
Ajax.delete('/api/delete/1');
```

### Examples

#### Basic POST Request

```javascript
var ajax = new Ajax({
    url: '/api/contact',
    method: 'POST',
    data: {
        name: 'John Doe',
        email: 'john@example.com',
        message: 'Hello!'
    },
    dataType: 'json',
    timeout: 30000,
    beforeSend: function() {
        console.log('Sending request...');
    },
    success: function(data) {
        console.log('Success:', data);
        alert(data.message);
    },
    error: function(error) {
        console.error('Error:', error);
        alert('An error occurred');
    },
    complete: function() {
        console.log('Request completed');
    }
});

ajax.execute();
```

#### Using FormData

```javascript
var formData = new FormData(document.querySelector('#upload-form'));

new Ajax({
    url: '/api/upload',
    method: 'POST',
    data: formData,
    dataType: 'json',
    success: function(response) {
        console.log('File uploaded:', response);
    },
    error: function(error) {
        console.error('Upload failed:', error);
    }
}).execute();
```

#### Chainable API

```javascript
new Ajax()
    .setUrl('/api/endpoint')
    .setMethod('POST')
    .setData({ name: 'John' })
    .setDataType('json')
    .setTimeout(30000)
    .beforeSend(function() { console.log('Loading...'); })
    .success(function(data) { console.log(data); })
    .error(function(error) { console.error(error); })
    .complete(function() { console.log('Done'); })
    .execute();
```

---

## ComponentAjax Class

Base AJAX class for Bitrix component actions. Supports both BX.ajax and vanilla JavaScript.

### Constructor

```javascript
new ComponentAjax(component, mode)
```

#### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `component` | string | required | Component name (e.g., 'aidsoul:forms') |
| `mode` | string | `'class'` | AJAX mode: 'class' or 'function' |

### Methods

#### setParams(params)

Set parameters for AJAX request.

```javascript
componentAjax.setParams({ form: 'contact', name: 'John' });
```

#### setMode(mode)

Set AJAX mode.

```javascript
componentAjax.setMode('class');
```

#### successAction(successFunction)

Set success callback.

```javascript
componentAjax.successAction(function(data, response) {
    console.log('Success:', data);
});
```

#### errorAction(errorFunction)

Set error callback.

```javascript
componentAjax.errorAction(function(error) {
    console.error('Error:', error);
});
```

#### beforeSendAction(beforeSendFunction)

Set before send callback.

```javascript
componentAjax.beforeSendAction(function() {
    console.log('Sending...');
});
```

#### completeAction(completeFunction)

Set complete callback.

```javascript
componentAjax.completeAction(function(response) {
    console.log('Complete:', response);
});
```

#### execute()

Execute AJAX request using BX.ajax.

```javascript
componentAjax.execute();
```

#### executeVanilla()

Execute AJAX request using vanilla JavaScript (without BX.ajax).

```javascript
componentAjax.executeVanilla();
```

### Example

```javascript
var ajax = new ComponentAjax('aidsoul:forms', 'class');
ajax.setMethod('ajax');
ajax.setParams({ 
    form: 'contact',
    name: 'John Doe',
    email: 'john@example.com'
});

ajax.beforeSendAction(function() {
    console.log('Loading...');
});

ajax.successAction(function(data, response) {
    console.log('Success:', data);
    if (data.message) {
        alert(data.message);
    }
});

ajax.errorAction(function(error) {
    console.error('Error:', error);
    alert(error.message || 'An error occurred');
});

ajax.completeAction(function() {
    console.log('Complete');
});

// Use vanilla JavaScript instead of BX.ajax
ajax.executeVanilla();
```

---

## FormComponentAjax Class

Form AJAX handler class. Extends ComponentAjax.

### Constructor

```javascript
new FormComponentAjax(formSelector)
```

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `formSelector` | string/HTMLElement | Form selector or form element |

### Methods

#### submitForm(form, formName)

Submit form via AJAX.

```javascript
form.submitForm(formElement, 'contact');
```

#### prepareFormData(formData)

Prepare FormData for request.

```javascript
var data = form.prepareFormData(formData);
```

#### handleSuccess(form, data, response)

Handle successful response.

```javascript
form.handleSuccess(formElement, data, response);
```

#### handleError(form, error)

Handle error response.

```javascript
form.handleError(formElement, error);
```

#### showMessage(form, type, message)

Show message (success/error).

```javascript
form.showMessage(formElement, 'success', 'Message sent!');
```

#### clearMessages(form)

Clear all messages.

```javascript
form.clearMessages(formElement);
```

#### setLoading(form, loading)

Set loading state.

```javascript
form.setLoading(formElement, true); // Show loading
form.setLoading(formElement, false); // Hide loading
```

#### onSuccess(callback)

Set custom success callback.

```javascript
form.onSuccess(function(data, response) {
    console.log('Form submitted:', data);
});
```

#### onError(callback)

Set custom error callback.

```javascript
form.onError(function(error) {
    console.error('Form error:', error);
});
```

### Auto-initialization

Forms with `data-ajax="true"` are automatically initialized:

```html
<form data-ajax="true" data-form-name="contact">
    <input type="text" name="name" required>
    <input type="email" name="email" required>
    <textarea name="message"></textarea>
    <button type="submit">Send</button>
</form>
```

### Manual Initialization

```javascript
// Initialize with selector
var form = new FormComponentAjax('#my-form');

// With callbacks
var form = new FormComponentAjax('#my-form')
    .onSuccess(function(data, response) {
        console.log('Success:', data);
        alert(data.message);
    })
    .onError(function(error) {
        console.error('Error:', error);
        alert(error.message);
    });
```

### Events

The form dispatches custom events:

#### form-success

```javascript
document.querySelector('#my-form').addEventListener('form-success', function(e) {
    console.log('Form submitted successfully', e.detail.data);
    console.log('Full response', e.detail.response);
});
```

#### form-error

```javascript
document.querySelector('#my-form').addEventListener('form-error', function(e) {
    console.log('Form error', e.detail.error);
});
```

---

## Global Exports

All classes are available globally:

```javascript
window.Ajax              // Vanilla AJAX class
window.ComponentAjax    // Component AJAX handler
window.FormComponentAjax // Form AJAX handler
```
