<?php

namespace AidSoul\Bitrix\Form;

/**
 * Unknown Form
 * 
 * Default form class when no registered form is found
 * 
 * @author AidSoul <work-aidsoul@outlook.com>
 */
class Unknown extends Form
{
    /**
     * Form field configuration
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
            'placeholder' => 'Введите ваше имя'
        ],
        'phone' => [
            'name' => 'Телефон',
            'type' => 'tel',
            'required' => true,
            'min' => 10,
            'max' => 20,
            'placeholder' => '+7 (999) 999-99-99'
        ],
        'email' => [
            'name' => 'Email',
            'type' => 'email',
            'required' => false,
            'placeholder' => 'email@example.com'
        ],
        'message' => [
            'name' => 'Сообщение',
            'type' => 'textarea',
            'required' => false,
            'min' => 0,
            'max' => 1000,
            'placeholder' => 'Ваше сообщение...'
        ],
        'submit' => [
            'name' => 'Отправить',
            'type' => 'submit'
        ]
    ];

    /**
     * Success action - what to do when form is validated
     * 
     * @return array
     */
    public function successAction(): array
    {
        // Default success response
        return [
            'success' => true,
            'message' => 'Форма успешно отправлена!',
            'resetForm' => true
        ];
    }
}
