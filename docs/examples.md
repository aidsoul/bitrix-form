# Usage Examples

## Table of Contents

1. [Form Examples](#form-examples)
2. [AJAX Examples](#ajax-examples)
3. [Custom Validation](#custom-validation)
4. [File Upload](#file-upload)
5. [Email Notifications](#email-notifications)

---

## Form Examples

### Simple Contact Form

```php
// contact_form.php
namespace YourNamespace;

use AidSoul\Bitrix\Form\Form;

class ContactForm extends Form
{
    protected string $queryType = 'post';
    
    protected array $currentParams = [
        'name' => [
            'name' => 'Имя',
            'type' => 'text',
            'required' => true,
            'min' => 2,
            'max' => 100
        ],
        'email' => [
            'name' => 'Email',
            'type' => 'email',
            'required' => true
        ],
        'message' => [
            'name' => 'Сообщение',
            'type' => 'textarea',
            'required' => false
        ],
        'submit' => [
            'name' => 'Отправить',
            'type' => 'submit'
        ]
    ];
    
    public function successAction(): array
    {
        // Process form data
        return [
            'success' => true,
            'message' => 'Спасибо! Мы свяжемся с вами.',
            'resetForm' => true
        ];
    }
}
```

### Registration Form with Select

```php
// registration_form.php
namespace YourNamespace;

use AidSoul\Bitrix\Form\Form;

class RegistrationForm extends Form
{
    protected string $queryType = 'post';
    
    protected array $currentParams = [
        'first_name' => [
            'name' => 'Имя',
            'type' => 'text',
            'required' => true
        ],
        'last_name' => [
            'name' => 'Фамилия',
            'type' => 'text',
            'required' => true
        ],
        'email' => [
            'name' => 'Email',
            'type' => 'email',
            'required' => true
        ],
        'password' => [
            'name' => 'Пароль',
            'type' => 'password',
            'required' => true,
            'min' => 6
        ],
        'country' => [
            'name' => 'Страна',
            'type' => 'select',
            'required' => true,
            'options' => [
                'ru' => 'Россия',
                'ua' => 'Украина',
                'by' => 'Беларусь',
                'kz' => 'Казахстан'
            ]
        ],
        'agree' => [
            'name' => 'Согласен с условиями',
            'type' => 'checkbox',
            'required' => true
        ],
        'submit' => [
            'name' => 'Зарегистрироваться',
            'type' => 'submit'
        ]
    ];
    
    public function successAction(): array
    {
        // Registration logic
        return [
            'success' => true,
            'message' => 'Регистрация успешна!',
            'redirect' => '/profile',
            'redirectDelay' => 1500
        ];
    }
}
```

---

## AJAX Examples

### Using Ajax Class (Vanilla JS)

```javascript
// Submit data without form
var ajax = new Ajax({
    url: '/api/newsletter/subscribe',
    method: 'POST',
    data: {
        email: 'user@example.com',
        name: 'John Doe'
    },
    dataType: 'json',
    beforeSend: function() {
        document.getElementById('subscribe-btn').disabled = true;
        document.getElementById('subscribe-btn').textContent = 'Подписка...';
    },
    success: function(data) {
        alert(data.message || 'Подписка оформлена!');
        document.getElementById('subscribe-form').reset();
    },
    error: function(error) {
        alert('Ошибка: ' + (error.message || 'Попробуйте позже'));
    },
    complete: function() {
        document.getElementById('subscribe-btn').disabled = false;
        document.getElementById('subscribe-btn').textContent = 'Подписаться';
    }
});

ajax.execute();
```

### Using Static Helpers

```javascript
// GET request with params
Ajax.get('/api/products', { category: 'electronics', page: 1 })
    .then(function(data) {
        console.log('Products:', data);
    });

// POST request
Ajax.post('/api/contact', {
        name: 'John',
        email: 'john@example.com',
        message: 'Hello!'
    })
    .then(function(response) {
        console.log('Response:', response);
    })
    .catch(function(error) {
        console.error('Error:', error);
    });
```

### Using ComponentAjax

```javascript
// Call Bitrix component action without BX.ajax
var componentAjax = new ComponentAjax('aidsoul:forms', 'class');
componentAjax.setMethod('ajax');
componentAjax.setParams({
    form: 'contact',
    action: 'getFormFields'
});

componentAjax.beforeSendAction(function() {
    console.log('Loading fields...');
});

componentAjax.successAction(function(data) {
    console.log('Fields loaded:', data);
    // Populate form with received fields
});

componentAjax.errorAction(function(error) {
    console.error('Error loading fields:', error);
});

// Use vanilla JavaScript instead of BX.ajax
componentAjax.executeVanilla();
```

---

## Custom Validation

### Custom Field Validation

```php
// Custom validation method
protected function validatePhone(string $phone): string
{
    // Remove all non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Check length
    if (strlen($phone) < 10 || strlen($phone) > 12) {
        $this->setError('phone', 'Введите корректный номер телефона');
    }
    
    // Return sanitized value
    return '+' . $phone;
}
```

### Custom Form Validation

```php
// Override validate method
public function validate(): bool
{
    // Call parent validation
    $isValid = parent::validate();
    
    // Custom cross-field validation
    $email = $this->params['email'] ?? '';
    $confirmEmail = $this->params['confirm_email'] ?? '';
    
    if ($email !== $confirmEmail) {
        $this->setError('confirm_email', 'Email адреса не совпадают');
        $isValid = false;
    }
    
    // Check password strength
    $password = $this->params['password'] ?? '';
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password)) {
        $this->setError('password', 'Пароль должен содержать минимум 8 символов и заглавную букву');
        $isValid = false;
    }
    
    return $isValid;
}
```

---

## File Upload

### File Field Configuration

```php
// In currentParams
'file' => [
    'name' => 'Файл',
    'type' => 'file',
    'required' => false,
    'file' => [
        'maxCount' => 3,           // Max number of files
        'maxSize' => 5242880,      // Max file size (5MB)
        'fileTypes' => [           // Allowed MIME types
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ]
    ]
]
```

### JavaScript: Upload with Progress

```javascript
var formData = new FormData();
formData.append('file', document.querySelector('#file-input').files[0]);
formData.append('description', 'My file');

new Ajax({
    url: '/api/upload',
    method: 'POST',
    data: formData,
    dataType: 'json',
    progress: function(event) {
        if (event.lengthComputable) {
            var percentComplete = (event.loaded / event.total) * 100;
            console.log('Upload progress:', percentComplete + '%');
            document.getElementById('progress-bar').style.width = percentComplete + '%';
        }
    },
    success: function(data) {
        console.log('File uploaded:', data);
        alert('Файл загружен!');
    },
    error: function(error) {
        console.error('Upload failed:', error);
        alert('Ошибка загрузки');
    }
}).execute();
```

---

## Email Notifications

### Configure Email in Form

```php
protected array $mail = [
    'eventType' => 'FEEDBACK_FORM',
    'mailTemplateId' => 12,  // ID from Mail templates
    'siteId' => 's1'
];
```

### Send Email in Success Action

```php
public function successAction(): array
{
    // Get validated form data
    $name = $this->params['name'];
    $email = $this->params['email'];
    $message = $this->params['message'];
    
    // Send email notification
    CEvent::SendImmediate(
        'FEEDBACK_FORM',           // Event type
        's1',                      // Site ID
        [
            'NAME' => $name,
            'EMAIL' => $email,
            'MESSAGE' => $message,
            'DATE' => date('d.m.Y H:i')
        ]
    );
    
    return [
        'success' => true,
        'message' => 'Спасибо! Ваше сообщение отправлено.'
    ];
}
```

### Email Template Example

```html
Новая заявка с сайта

Имя: #NAME#
Email: #EMAIL#
Сообщение: #MESSAGE#
Дата: #DATE#
```

---

## Complete Example: Callback Form

### PHP Class

```php
// callback_form.php
namespace YourNamespace;

use AidSoul\Bitrix\Form\Form;

class CallbackForm extends Form
{
    protected string $queryType = 'post';
    
    protected array $currentParams = [
        'name' => [
            'name' => 'Ваше имя',
            'type' => 'text',
            'required' => true,
            'min' => 2,
            'placeholder' => 'Как к вам обращаться?'
        ],
        'phone' => [
            'name' => 'Телефон',
            'type' => 'tel',
            'required' => true,
            'validateMethod' => 'validatePhone'
        ],
        'call_time' => [
            'name' => 'Удобное время для звонка',
            'type' => 'select',
            'required' => false,
            'options' => [
                'any' => 'Любое время',
                'morning' => 'Утро (9-12)',
                'day' => 'День (12-18)',
                'evening' => 'Вечер (18-21)'
            ]
        ],
        'submit' => [
            'name' => 'Заказать звонок',
            'type' => 'submit'
        ]
    ];
    
    protected function validatePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) < 10) {
            $this->setError('phone', 'Введите корректный номер');
        }
        return '+' . $phone;
    }
    
    public function successAction(): array
    {
        // Save to database or CRM
        // \YourModule\CallbacksTable::add([...]);
        
        // Send notification
        CEvent::SendImmediate('CALLBACK_REQUEST', 's1', [
            'PHONE' => $this->params['phone'],
            'NAME' => $this->params['name'],
            'TIME' => $this->params['call_time'] ?? 'any'
        ]);
        
        return [
            'success' => true,
            'message' => 'Спасибо! Мы перезвоним вам в ближайшее время.',
            'resetForm' => true
        ];
    }
}
```

### HTML Template

```html
<form id="callback-form" data-ajax="true" data-form-name="callback">
    <div class="form-group">
        <label for="name">Ваше имя</label>
        <input type="text" name="name" id="name" required minlength="2">
        <span id="error-name" class="error"></span>
    </div>
    
    <div class="form-group">
        <label for="phone">Телефон</label>
        <input type="tel" name="phone" id="phone" required placeholder="+7 (999) 000-00-00">
        <span id="error-phone" class="error"></span>
    </div>
    
    <div class="form-group">
        <label for="call_time">Удобное время</label>
        <select name="call_time" id="call_time">
            <option value="any">Любое время</option>
            <option value="morning">Утро (9-12)</option>
            <option value="day">День (12-18)</option>
            <option value="evening">Вечер (18-21)</option>
        </select>
    </div>
    
    <div class="form-success" style="display: none;"></div>
    <div class="form-error" style="display: none;"></div>
    
    <button type="submit">Заказать звонок</button>
</form>
```

### JavaScript Initialization

```javascript
// Manual initialization with callbacks
var callbackForm = new FormComponentAjax('#callback-form')
    .onSuccess(function(data, response) {
        console.log('Callback requested:', data);
        // Track in analytics
        yaCounter12345678.reachGoal('callback_request');
    })
    .onError(function(error) {
        console.error('Error:', error);
    });
```

---

## Troubleshooting

### Common Issues

#### CORS Errors

If using cross-domain requests, ensure server sends proper headers:

```php
// In PHP
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
```

#### File Upload Size

Check `upload_max_filesize` and `post_max_size` in php.ini.

#### Session Issues

For AJAX requests, ensure session is started:

```php
// In your PHP handler
session_start();
```
