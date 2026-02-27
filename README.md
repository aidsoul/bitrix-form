# Bitrix Forms Component

AJAX-форма компонент для Bitrix CMS с валидацией и отправкой данных.

## Documentation

For detailed documentation, see the [docs](docs/) folder:

- [Documentation Index](docs/index.md) - Overview and navigation
- [JavaScript API](docs/js-api.md) - Complete JavaScript API reference
- [PHP API](docs/php-api.md) - Complete PHP API reference
- [Examples](docs/examples.md) - Common usage scenarios

## Quick Start

### Installation

1. Copy the `local/components/aidsoul/forms` folder to your project
2. Copy the `src` folder to `/local/php_interface/` or configure autoload

### Component Call

```php
$APPLICATION->IncludeComponent(
    "aidsoul:forms", 
    ".default",
    array(
        "AJAX" => "Y",
        "FORM_NAME" => "contact",
        "FORM_ID" => "contact-form",
        "TITLE" => "Связаться с нами",
        "SUBMIT_TEXT" => "Отправить заявку"
    )
);
```

### JavaScript AJAX (without BX.ajax)

```javascript
// Using vanilla JavaScript Ajax class
new Ajax({
    url: '/api/endpoint',
    method: 'POST',
    data: { name: 'John' },
    success: function(data) { console.log(data); }
}).execute();

// Or using static helpers
Ajax.post('/api/submit', { name: 'John' })
    .then(function(data) { console.log(data); });
```

## Features

- AJAX form submission without page reload
- Server-side validation
- File uploads
- CSRF protection
- Flexible field configuration
- Email event support
- Custom validation methods
- Error message localization
- Vanilla JavaScript AJAX class without BX.ajax dependency
