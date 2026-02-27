# PHP API

## Table of Contents

1. [Installation](#installation)
2. [Component Usage](#component-usage)
3. [Form Class](#form-class) - Base form class
4. [ContactForm Class](#contactform-class) - Contact form example

---

## Installation

1. Copy the `local/components/aidsoul/forms` folder to your project
2. Copy the `src` folder to `/local/php_interface/` or configure autoload

### Composer Autoload

Add to your `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "AidSoul\\Bitrix\\Form\\": "src/"
        }
    }
}
```

Then run `composer dump-autoload`.

---

## Component Usage

### Basic Component Call

```php
$APPLICATION->IncludeComponent(
    "aidsoul:forms", 
    ".default",
    array(
        "AJAX" => "Y",
        "FORM_NAME" => "contact",
        "FORM_ID" => "contact-form",
        "FORM_CLASS" => "my-form-class",
        "TITLE" => "Связаться с нами",
        "SUBMIT_TEXT" => "Отправить заявку",
        "POLITIC_URL" => "/politic/",
        "CACHE_TIME" => 3600,
        "FORM" => array(
            // Additional form parameters
        )
    )
);
```

### Component Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `AJAX` | string | `'Y'` | Enable AJAX mode |
| `FORM_NAME` | string | required | Form name for registration |
| `FORM_ID` | string | `''` | Form ID attribute |
| `FORM_CLASS` | string | `''` | Form class attribute |
| `TITLE` | string | `''` | Form title |
| `SUBMIT_TEXT` | string | `'Отправить'` | Submit button text |
| `POLITIC_URL` | string | `''` | Privacy policy URL |
| `CACHE_TIME` | int | `3600` | Cache time in seconds |

### Registering Forms

Create your component class and register forms:

```php
// In your component class
class Forms extends \CBitrixComponent
{
    public static function registerForms(): array
    {
        return [
            'contact' => \AidSoul\Bitrix\Form\ContactForm::class,
            'callback' => \AidSoul\Bitrix\Form\CallbackForm::class,
            'order' => \Your\Namespace\OrderForm::class,
        ];
    }
}
```

---

## Form Class

Base class for creating forms. All custom forms should extend this class.

### Class Definition

```php
namespace AidSoul\Bitrix\Form;

use Bitrix\Main\Entity;

class Form
{
    // Properties
    protected string $queryType = 'post';
    protected array $mail = [];
    protected array $currentParams = [];
    protected array $errors = [];
    protected array $result = [];
}
```

### Properties

#### $queryType

Request type: `get`, `post`, or `all`.

```php
protected string $queryType = 'post';
```

#### $mail

Email configuration.

```php
protected array $mail = [
    'eventType' => 'FORM_SUBMIT',
    'mailTemplateId' => 10
];
```

#### $currentParams

Form field configuration.

```php
protected array $currentParams = [
    'name' => [
        'name' => 'Имя',
        'type' => 'text',
        'required' => true,
        'min' => 2,
        'max' => 100,
        'placeholder' => 'Ваше имя'
    ],
    'email' => [
        'name' => 'Email',
        'type' => 'email',
        'required' => true
    ]
];
```

### Field Types

- `text` - Text input
- `email` - Email input
- `tel` - Phone input
- `password` - Password input
- `number` - Number input
- `textarea` - Text area
- `select` - Dropdown select
- `checkbox` - Checkbox
- `file` - File upload
- `hidden` - Hidden input
- `submit` - Submit button

### Field Configuration

```php
'field_name' => [
    'name' => 'Display Name',
    'type' => 'text',
    'required' => true/false,
    'min' => 2,              // Min length
    'max' => 100,            // Max length
    'placeholder' => 'Hint',
    'value' => 'Default value',
    'validateMethod' => 'methodName', // Custom validation
    'options' => [           // For select
        'key1' => 'Value 1',
        'key2' => 'Value 2'
    ],
    'file' => [              // For file
        'maxCount' => 3,
        'maxSize' => 5242880, // 5MB
        'fileTypes' => ['image/jpeg', 'image/png']
    ]
]
```

### Methods

#### validate()

Validate form data. Called automatically before `successAction()`.

```php
public function validate(): bool
{
    // Custom validation logic
    return empty($this->errors);
}
```

#### successAction()

Handle successful form submission. Must return an array.

```php
public function successAction(): array
{
    // Save to database, send email, etc.
    return [
        'success' => true,
        'message' => 'Спасибо! Заявка принята.',
        'resetForm' => true
    ];
}
```

#### setError()

Set validation error.

```php
protected function setError(string $field, string $message): void
{
    $this->errors[$field] = $message;
}
```

#### getErrors()

Get all validation errors.

```php
public function getErrors(): array
{
    return $this->errors;
}
```

### Example: Custom Form

```php
<?php

namespace Your\Namespace;

use AidSoul\Bitrix\Form\Form;

class OrderForm extends Form
{
    // Request type
    protected string $queryType = 'post';
    
    // Email configuration
    protected array $mail = [
        'eventType' => 'ORDER_FORM',
        'mailTemplateId' => 10
    ];
    
    // Form fields
    protected array $currentParams = [
        'name' => [
            'name' => 'Имя',
            'type' => 'text',
            'required' => true,
            'min' => 2,
            'max' => 100,
            'placeholder' => 'Ваше имя'
        ],
        'email' => [
            'name' => 'Email',
            'type' => 'email', 
            'required' => true
        ],
        'phone' => [
            'name' => 'Телефон',
            'type' => 'tel',
            'required' => true,
            'validateMethod' => 'validatePhone'
        ],
        'product' => [
            'name' => 'Товар',
            'type' => 'select',
            'required' => true,
            'options' => [
                'product1' => 'Товар 1',
                'product2' => 'Товар 2'
            ]
        ],
        'message' => [
            'name' => 'Сообщение',
            'type' => 'textarea',
            'required' => false
        ],
        'file' => [
            'name' => 'Файл',
            'type' => 'file',
            'required' => false,
            'file' => [
                'maxCount' => 3,
                'maxSize' => 5242880,
                'fileTypes' => ['image/jpeg', 'image/png', 'application/pdf']
            ]
        ],
        'submit' => [
            'name' => 'Заказать',
            'type' => 'submit'
        ]
    ];
    
    // Custom phone validation
    protected function validatePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) < 10) {
            $this->setError('phone', 'Введите корректный номер');
        }
        return strip_tags($phone);
    }
    
    // Success handler
    public function successAction(): array
    {
        // Save to database
        // $this->params contains validated form data
        
        // Send email notification
        // CEvent::Send($this->mail['eventType'], 's1', [...]);
        
        return [
            'success' => true,
            'message' => 'Спасибо! Заявка принята.',
            'resetForm' => true
        ];
    }
}
```

---

## ContactForm Class

Example form class included in the package.

### Location

`src/ContactForm.php`

### Features

- Name field (text, required)
- Email field (email, required)
- Phone field (tel, optional)
- Message field (textarea, optional)
- File upload support
- Email notifications

### Usage

```php
// Register in component
public static function registerForms(): array
{
    return [
        'contact' => \AidSoul\Bitrix\Form\ContactForm::class,
    ];
}
```

### Render in template

```php
// In component template
$APPLICATION->IncludeComponent(
    "aidsoul:forms", 
    ".default",
    array(
        "FORM_NAME" => "contact"
    )
);
```

---

## Response Format

### Success Response

```php
public function successAction(): array
{
    return [
        'success' => true,
        'message' => 'Success message',
        'resetForm' => true,        // Reset form after submit
        'hideForm' => false,         // Hide form after submit
        'redirect' => '/thank-you', // Redirect URL
        'redirectDelay' => 2000,    // Redirect delay in ms
        'data' => [                  // Custom data
            'orderId' => 123
        ]
    ];
}
```

### Error Response

```php
// Validation errors
$this->setError('email', 'Invalid email');
$this->setError('name', 'Name is required');

// Custom error data
return [
    'success' => false,
    'message' => 'Error message',
    'errors' => [
        [
            'code' => 'ERROR_CODE',
            'message' => 'Error message',
            'customData' => [
                'fields' => [
                    'email' => 'Invalid email format'
                ]
            ]
        ]
    ]
];
```
