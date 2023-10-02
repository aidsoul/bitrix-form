<?php

namespace AidSoul\Bitrix\Form;

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;

/**
 * Класс для работы с данными  поля формы
 * Запуск через BX.ajax.runComponentAction
 * ajaxAction
 *
 * @author work-aidsoul@outlook.com
 */
abstract class Form
{
    private ErrorCollection $errors;
    /**
     * Массив полей
     * необходим для работы с ИБ
     * @var array
     */
    protected array $cleanFields = [];

    /**
     *  'fio' => [
     * 'name' => 'ФИО',
     * 'required' => true
     * ],
     * @var array
     */
    protected array $formFields = [];

    /**
     *   'eventType' => 'Тип почтового события',
     *   'mailTemplateId' => 'ID почтового шаблона'
     *
     * @var array
     */
    protected array $mail = [];
    protected array $mailAttachments = [];

    /**
     * Ajax только для авторизированных пользователей
     *
     * @var boolean
     */
    protected bool $ajaxUserAuthorization = false;

    /**
     *
     * @var string
     */
    protected array $modalArr = [];

    /**
     * success data array
     *
     * @var array
     */
    protected array $customData = [];
    protected array $reply = [];
    protected array $formData = [];

    public function __construct()
    {
        $this->errors = new ErrorCollection();
    }

    /**
     * Установка ошибки
     *
     * @param string $message
     * @param string $name
     * @param array $data
     * @return void
     */
    protected function setError(string $code, string|array $message, array $data = []): void
    {
        if (!empty($code)) {
            if (!$this->errors->getErrorByCode($code)) {
                $this->errors->setError(new Error($message, $code, $data));
            }
        }
    }

    /**
     * Функция валидации полей
     *
     * @param array $params
     * @return void
     */
    public function validation(array $params = []): void
    {
        $auth = false;
        if ($this->ajaxUserAuthorization === true) {
            global $USER;
            if (!$USER->IsAuthorized()) {
                $this->setError('error', 'Authorization error');
                return;
            }
        }
        if (!empty($params) && $auth === false) {
            foreach ($params as $k => $param) {
                if (is_array($param)) {
                    if (method_exists($this, $k)) {
                        $this->$k($param);
                    }
                } else {
                    if (method_exists($this, $k)) {
                        if (preg_match("/<[^<]+>/", $param)) {
                            $this->setError(
                                $k,
                                'В поле "' . $this->formFields[$k]['name'] . '" недопустимые символы!'
                            );
                        }
                        if ($this->formFields[$k]['required'] === true && empty($param)) {
                            $this->setError(
                                $k,
                                'Поле "' . $this->formFields[$k]['name'] . '" обязательное для заполнения'
                            );
                        }
                        $this->$k(strip_tags(htmlspecialcharsbx($param)));
                    } else {
                        $this->setError('field', 'error');
                    }
                }
            }
        }
    }

    /**
     * Подготовительное действие
     *
     * @return array
     */
    public function prepareAction(): void
    {
    }

    /**
     * Если валидация пройдена
     *
     * @return array
     */
    public function successAction(): void
    {
    }


    /**
     * Действие с данными формы
     *
     *
     * @return array
     */
    public function formDataAction(): array
    {
        return [];
    }


    /**
     * Установить данные для формы
     *
     * $this->formData
     *
     * @param array $formData
     * @return void
     */
    public function setFormData(array $formData): void
    {
        $this->formData = $formData;
    }

    /**
     * Получить данные формы
     *
     * @return array
     */
    public function getFormData(): array
    {
        return $this->formData;
    }

    public function getErrors(): ErrorCollection
    {
        return $this->errors;
    }

    /**
     * pageTitle
     *
     * @param string $pageTitle
     * @return void
     */
    private function pageTitle(string $pageTitle)
    {
        $this->cleanFields['PAGE_TITLE'] = $pageTitle;
    }

    /**
     * Отправить сообщение на почту
     *
     * @return void
     */
    public function sendMail(): void
    {
        if (!empty($this->mail)) {
            \CEvent::Send(
                $this->mail['eventType'],
                SITE_ID,
                $this->cleanFields,
                'N',
                $this->mail['mailTemplateId'],
                $this->mailAttachments
            );
        }
    }

    /**
     * Получить успешный ответ
     *
     * @return array
     */
    public function getSuccessResponse(): array
    {
            return [
                'modal' => $this->modalArr,
                'customData' => $this->customData,
                'fields' => $this->cleanFields
            ];
    }
}
