# Bitrix Forms Component

AJAX-форма компонент для Bitrix CMS с валидацией и отправкой данных.

## Установка

1. Скопируйте папку `local/components/aidsoul/forms` в ваш проект
2. Скопируйте папку `src` в `/local/php_interface/` или подключите через autoload

## Использование

### Вызов компонента

```php
$APPLICATION->IncludeComponent(
    "aidsoul:forms", 
    ".default",
    array(
        "AJAX" => "Y",
        "FORM_NAME" => "contact", // Имя формы для регистрации
        "FORM_ID" => "contact-form",
        "FORM_CLASS" => "my-form",
        "TITLE" => "Связаться с нами",
        "SUBMIT_TEXT" => "Отправить заявку",
        "POLITIC_URL" => "/politic/",
        "CACHE_TIME" => 3600,
        "FORM" => array(
            // Дополнительные параметры формы
        )
    )
);
```

### Регистрация форм

Создайте свой класс формы и зарегистрируйте его в компоненте:

```php
// В вашем классе компонента
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

### Создание формы

Создайте класс формы, наследующий `AidSoul\Bitrix\Form\Form`:

```php
<?php

namespace Your\Namespace;

use AidSoul\Bitrix\Form\Form;

class OrderForm extends Form
{
    // Тип запроса: get|post|all
    protected string $queryType = 'post';
    
    // Конфигурация почты (если нужно)
    protected array $mail = [
        'eventType' => 'ORDER_FORM',
        'mailTemplateId' => 10
    ];
    
    // Поля формы
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
            'validateMethod' => 'validatePhone' // Свой метод валидации
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
                'maxSize' => 5242880, // 5MB
                'fileTypes' => ['image/jpeg', 'image/png', 'application/pdf']
            ]
        ],
        'submit' => [
            'name' => 'Заказать',
            'type' => 'submit'
        ]
    ];
    
    // Кастомная валидация телефона
    protected function validatePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) < 10) {
            $this->setError('phone', 'Введите корректный номер');
        }
        return strip_tags($phone);
    }
    
    // Действие при успешной валидации
    public function successAction(): array
    {
        // Сохранение в БД, отправка email и т.д.
        return [
            'success' => true,
            'message' => 'Спасибо! Заявка принята.',
            'resetForm' => true
        ];
    }
}
```

### Типы полей

- `text` - текстовое поле
- `email` - email поле
- `tel` - телефон
- `password` - пароль
- `number` - число
- `textarea` - текстовая область
- `select` - выпадающий список
- `checkbox` - чекбокс` - за
- `fileгрузка файла
- `hidden` - скрытое поле
- `submit` - кнопка отправки

### Конфигурация поля

```php
'field_name' => [
    'name' => 'Отображаемое имя',
    'type' => 'text',
    'required' => true/false,
    'min' => 2,           // мин. длина
    'max' => 100,         // макс. длина
    'placeholder' => 'Подсказка',
    'value' => 'Значение по умолчанию',
    'validateMethod' => 'methodName', // свой метод валидации
    'options' => [         // для select
        'key1' => 'Value 1',
        'key2' => 'Value 2'
    ],
    'file' => [           // для file
        'maxCount' => 3,
        'maxSize' => 5242880,
        'fileTypes' => ['image/jpeg', 'image/png']
    ]
]
```

## JavaScript API

### Инициализация формы

```javascript
// Автоматическая инициализация (для форм с data-ajax="true")
<form data-ajax="true" data-form-name="contact">
    ...
</form>

// Ручная инициализация
var form = new FormComponentAjax('#my-form');

// С колбэками
var form = new FormComponentAjax('#my-form')
    .onSuccess(function(data) {
        console.log('Success:', data);
    })
    .onError(function(error) {
        console.log('Error:', error);
    });
```

### Методы

```javascript
// Отправить форму программно
form.submitForm(formElement, formName);

// Установить параметры
form.setParams({key: 'value'});

// Выполнить запрос
form.execute();
```

### События

```javascript
document.querySelector('#my-form').addEventListener('form-success', function(e) {
    console.log('Form submitted successfully', e.detail.data);
});

document.querySelector('#my-form').addEventListener('form-error', function(e) {
    console.log('Form error', e.detail.error);
});
```

## Структура файлов

```
local/components/aidsoul/forms/
├── class.php              # Основной класс компонента
├── forms.js               # JavaScript для AJAX
├── .description.php       # Описание компонента
├── .parameters.php        # Параметры компонента
└── templates/
    └── .default/
        ├── template.php   # Шаблон формы
        ├── style.css      # Стили
        └── script.js      # Дополнительные скрипты

src/
├── Form.php              # Базовый класс формы
├── ContactForm.php       # Пример формы
└── Unknown.php           # Форма по умолчанию
```

## Особенности

- AJAX отправка без перезагрузки страницы
- Валидация на стороне сервера
- Загрузка файлов
- Защита от CSRF
- Гибкая настройка полей
- Поддержка почтовых событий
- Кастомные методы валидации
- Локализация сообщений об ошибках
