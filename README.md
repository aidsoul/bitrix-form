# Example class

```php
<?php 

namespace AidSoul\Bitrix\Form;

use Bitrix\Main\UserTable;
use Respect\Validation\Validator;
use Bitrix\Iblock\Elements\ElementRequestFormTable;

/**
 * Request
 * @author AidSoul <work-aidsoul@outlook.com>
 */
class Request extends Form
{
    /**
     * Массив для работы с почтой
     * 
     * @var array
     */
    protected array $mail = [
        'eventType' => 'REQUEST_FORM',
        'mailTemplateId' => 89
    ];

    protected array $modalArr = [
        'title' => 'Заявка отправлена!',
        'message' => 'Мы ответим Вам в ближайшее время'
    ];

    /**
     * Поля текущей формы
     * 
     *  @var array
     */
    protected array $formFields = [
        'email' => [
            'name' => 'E-mail',
            'required' => true
        ],
        'phone' => [
            'name' => 'Телефон',
            'required' => true
        ]
    ];

    /**
     * Валидация поля "Телефон"
     * 
     *  @param string $phone
     * 
     *  @var void
     */
    protected function phone(string $phone): void
    {
        if (!Validator::phone()->length(12, 12)->validate($phone)) {
            $this->setError('phone', 'Неверный формат поля "Телефон"!');
        } else {
            $this->cleanFields['PHONE'] = $phone;
        }
    }

    /**
     * Валидация поля "Имя"
     *
     * @param string $name
     * 
     * @return void
     */
    protected function name(string $name): void
    {
        if (!Validator::length(2, 45)->validate($name)) {
            $this->setError('name', 'Строка поля "Имя", должна содержать от 2 до 45 символов');
        }
        $this->cleanFields['NAME'] = $name;
    }

    /**
     * Возвращает массив, который доступен в шаблоне 
     * $arResult['DATA']
     *
     * @return array
     */
    public function formDataAction(): array
    {
        $table = getClassByCodeApi($this->formData['API_CODE'] ?? 'RequestForm');
        return $table::query()
        ->setSelect([
            'ID',
            'NAME',
            'CODE',
            'SUBTITLE_' => 'SUBTITLE',
            'IMAGE_' => 'IMAGE',
        ])
        ->setOrder(['SORT' => 'ASC'])
        ->setFilter(['=CODE' => $this->formData['ELEMENT_CODE']])
        ->setCacheTtl(53456)->fetch();
    }
}
```
## 
