<?php

namespace AidSoul\Bitrix\Form;

use Respect\Validation\Validator;

/**
 * Contact Form Example
 * 
 * This is an example of how to create a custom form class
 * 
 * @author AidSoul <work-aidsoul@outlook.com>
 */
class ContactForm extends Form
{
    /**
     * Query type: get|post|all
     * 
     * @var string
     */
    protected string $queryType = 'post';

    /**
     * Mail configuration (if needed)
     * 
     * @var array
     */
    protected array $mail = [
        'eventType' => 'CONTACT_FORM',
        'mailTemplateId' => 0 // Set your mail template ID
    ];

    /**
     * Form fields configuration
     * 
     * @var array
     */
    protected array $currentParams = [
        'name' => [
            'name' => 'Имя',
            'type' => 'text',
            'required' => true,
            'min' => 2,
            'max' => 100,
            'placeholder' => 'Введите ваше имя',
            'validateMethod' => 'validateName'
        ],
        'email' => [
            'name' => 'Email',
            'type' => 'email',
            'required' => true,
            'placeholder' => 'email@example.com',
            'validateMethod' => 'validateEmail'
        ],
        'phone' => [
            'name' => 'Телефон',
            'type' => 'tel',
            'required' => false,
            'placeholder' => '+7 (999) 999-99-99',
            'validateMethod' => 'validatePhone'
        ],
        'subject' => [
            'name' => 'Тема',
            'type' => 'select',
            'required' => true,
            'options' => [
                'general' => 'Общий вопрос',
                'support' => 'Техническая поддержка',
                'partnership' => 'Сотрудничество',
                'other' => 'Другое'
            ]
        ],
        'message' => [
            'name' => 'Сообщение',
            'type' => 'textarea',
            'required' => true,
            'min' => 10,
            'max' => 2000,
            'placeholder' => 'Ваше сообщение...'
        ],
        'file' => [
            'name' => 'Файл',
            'type' => 'file',
            'required' => false,
            'file' => [
                'maxCount' => 3,
                'maxSize' => 5242880, // 5MB
                'fileTypes' => [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ]
            ]
        ],
        'politic' => [
            'name' => 'Согласие на обработку',
            'type' => 'checkbox',
            'required' => true
        ],
        'submit' => [
            'name' => 'Отправить',
            'type' => 'submit'
        ]
    ];

    /**
     * Validate name field
     * 
     * @param string $name
     * @return string
     */
    protected function validateName(string $name): string
    {
        if (!Validator::length(2, 100)->validate($name)) {
            $this->setError('name', 'Имя должно содержать от 2 до 100 символов');
        }
        
        return strip_tags($name);
    }

    /**
     * Validate email field
     * 
     * @param string $email
     * @return string
     */
    protected function validateEmail(string $email): string
    {
        if (!Validator::email()->validate($email)) {
            $this->setError('email', 'Введите корректный email адрес');
        }
        
        return strip_tags($email);
    }

    /**
     * Validate phone field
     * 
     * @param string $phone
     * @return string
     */
    protected function validatePhone(string $phone): string
    {
        // Remove all non-digit characters
        $phoneDigits = preg_replace('/[^0-9]/', '', $phone);
        
        if (!empty($phone) && strlen($phoneDigits) < 10) {
            $this->setError('phone', 'Введите корректный номер телефона');
        }
        
        return strip_tags($phone);
    }

    /**
     * Validate politic checkbox
     * 
     * @param string $value
     * @return string
     */
    protected function politic(string $value): string
    {
        if ($value !== 'Y') {
            $this->setError('politic', 'Необходимо согласие на обработку персональных данных');
        }
        
        return $value;
    }

    /**
     * Action to perform when form is successfully validated
     * 
     * @return array
     */
    public function successAction(): array
    {
        // Example: Save to database
        // $this->saveToDatabase();
        
        // Example: Send email notification
        // $this->sendNotification();
        
        return [
            'success' => true,
            'message' => 'Спасибо! Ваша заявка принята. Мы свяжемся с вами в ближайшее время.',
            'resetForm' => true,
            'hideForm' => false
        ];
    }

    /**
     * Save form data to database (example)
     * 
     * @return void
     */
    private function saveToDatabase(): void
    {
        // Example implementation:
        // $connection = \Bitrix\Main\Application::getConnection();
        // $connection->query("INSERT INTO forms_log (name, email, message, created_at) VALUES (
        //     '{$this->cleanParams['name']}',
        //     '{$this->cleanParams['email']}',
        //     '{$this->cleanParams['message']}',
        //     NOW()
        // )");
    }

    /**
     * Send notification (example)
     * 
     * @return void
     */
    private function sendNotification(): void
    {
        // Email is sent automatically if $this->mail is configured
    }

    /**
     * Get data for template
     * 
     * @return array
     */
    protected function arResultAction(): array
    {
        return [
            'FORM_TITLE' => 'Связаться с нами',
            'FORM_SUBTITLE' => 'Оставьте заявку, и мы ответим вам'
        ];
    }
}
