<?php

namespace AidSoul\Forms;

use Bitrix\Main\Context;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;

/**
 * Form
 *
 * @author work-aidsoul@outlook.com
 * @license MIT
 *
 */
abstract class Form
{
    /**
     * @var ErrorCollection
     */
    protected ErrorCollection $errorCollection;

    /**
     * @var Context
     */
    protected Context $context;

    /**
     * Параметры получаемые из GET/POST/FILES
     *
     * @var array
     */
    protected array $params = [];

    /**
     * Игнорировать проверку следующих параметров
     *
     * @var array
     */
    protected array $ignoreFieldArr = [];

    /**
     * Массив параметров после очистки
     * @var array
     */
    protected array $cleanParams = [];
    /**
     * Массив неочищенных параметров
     *
     * @var array
     */
    protected array $dirtyParams = [];

    private array $arResult = [];

    protected array $arParams = [];

    /**
     * Тип запроса
     * get|post|all
     *
     * @var string
     */
    protected string $queryType = 'post';

    /**
     * Параметры, которые принадлежат форме
     *
     * 'fio'        => [
     * 'name'       => 'ФИО',
     * 'required'   => true
     * 'min'        => 1,
     * 'max'        => 3,
     * 'regular'    => [
     *      'emailTest' =>  [
     *       'rule'     => '/email@mail/s',
     *        'message' => 'Не email@mail.ru'
     *            ]
     *       ],
     *   'file' => [
     *       'maxSize' => 5242880,
     *         'fileTypes' => [
     *          'image/jpeg',
     *          'image/jpg',
     *          'image/png'
     *    ]
     *  ]
     * ],
     * @var array
     */
    protected array $currentParams = [];

    private array $preFilters = [];
    private array $postFilters = [];

    /**
     * Если нужна работа с почтой
     *
     *  'eventType' => 'Тип почтового события',
     *  'mailTemplateId' => 'ID почтового шаблона'
     *
     * @var array
     */
    protected array $mail = [];

    /**
     * Почтовые вложения
     *
     * @var array
     */
    protected array $mailAttachments = [];

    /**
     * success data array
     *
     * @var array
     */
    private array $replyData = [];

    public function __construct()
    {
        $this->errorCollection = new ErrorCollection();
        $this->preFilters = $this->preFilters();
        $this->postFilters = $this->postFilters();
    }

    protected function arResultAction(): array
    {
        return [];
    }

    public function getArResult(): array
    {
        return $this->arResultAction();
    }

    /**
     * setArParams function
     *
     * @param array $arParams
     * @return self
     */
    public function setArParams(array $arParams): self
    {
        $this->arParams = $arParams;
        return $this;
    }


    /**
     * Получить все параметры запроса
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Установка ошибки
     *
     * @param string $message
     * @param string $name
     * @param array $data
     * @return void
     */
    protected function setError(
        string $code,
        string|array $message,
        array $data = []
    ): void {
        if (!$this->errorCollection->getErrorByCode($code)) {
            $this->errorCollection->setError(new Error($message, $code, $data));
        }
    }

    /**
     * Установить параметры
     *
     * @param array $params
     * @return self
     */
    public function setParams(array $params): self
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Min/Max string validation function
     *
     * @param string|integer $str
     * @param string $param
     * @param integer|null $min
     * @param integer|null $max
     * @return void
     */
    private function strLengthValidation(string|int $str, string $param, int|null $min, int|null $max): void
    {
        $symbolsValue = mb_strlen($str, "UTF-8");
        if ($min && $symbolsValue < $min) {
            $this->setError('minCharacters', 'Минимальное количество символов для поля "' . $param . '" = ' . $min);
        }
        if ($max && $symbolsValue > $max) {
            $this->setError('maxCharacters', 'Максимальное количество символов для поля "' . $param . '" = ' . $max);
        }
    }

    /**
     * Regular Validation
     *
     * @param array $regularArr
     * @param string $value
     * @param string $param
     * @return void
     */
    private function regularValidation(array $regularArr, string $value, string $param): void
    {
        foreach ($regularArr as $k => $regular) {
            if (!preg_match($regular['rule'], $value)) {
                $message = $regular['message'];
                if (!$message) {
                    $message = 'Поле "' . $param . '" не соответствует шаблону';
                }
                $this->setError($k, $message);
            }
        }
    }

    /**
     * validateBaseParams function
     *
     * @param string $param
     * @param string $value
     * @return void
     */
    private function validateBaseParams(string &$param, string &$value): void
    {
        $value = strip_tags(htmlspecialcharsbx($value));
        $currentParams = &$this->currentParams[$param];
        $currentParamsName = $currentParams['name'] ?? '';
        if ($currentParams) {
            if (!in_array($param, $this->ignoreFieldArr)) {
                if (preg_match("/[<>\/]+/ium", $value) && !$currentParams['noBaseRegular']) {
                    $this->setError(
                        'invalidCharacters',
                        'В поле "' . $currentParamsName . '" недопустимые символы!'
                    );
                } else {
                    $this->strLengthValidation($value, $currentParamsName, $currentParams['min'], $currentParams['max']);
                    $this->regularValidation($currentParams['regular'] ?? [], $value, $currentParamsName);
                    if ($this->errorCollection->isEmpty()) {
                        $method = $param;
                        if ($currentParams['validateMethod']) {
                            $method = $currentParams['validateMethod'];
                        }
                        $newValue = $value;
                        if (method_exists($this, $method)) {
                            $newValue = $this->$method($value);
                        }
                        $this->cleanParams[$param] = $newValue;
                    }
                }
            }
        } else {
            $this->dirtyParams[$param] = $value;
        }
    }

    /**
     * validateFileParams function
     *
     * @param string $param
     * @param array $value
     * @return void
     */
    private function validateFileParams(string &$param, array &$value): void
    {
        $count = count($value['name']);
        $currentParam = &$this->currentParams[$param]['file'] ?? false;
        $newValue = [];
        if ($currentParam) {
            $formatBytes = function ($bytes) {
                if ($bytes > 0) {
                    $i = floor(log($bytes) / log(1024));
                    $sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
                    return sprintf('%.02F', round($bytes / pow(1024, $i), 1)) * 1 . ' ' . @$sizes[$i];
                } else {
                    return 0;
                }
            };
            for ($i = 0; $i < $count; $i++) {
                $tmpName = $value['tmp_name'][$i] ?? false;
                $fileName = $value['name'][$i];
                $fileType = $value['type'][$i];
                $fileSize = $value['size'][$i];
                if ($tmpName === false) {
                    continue;
                }
                if ($maxSize = $currentParam['maxSize']) {
                    if ($fileSize > $maxSize) {
                        $this->setError('fileMaxSize', 'Максимальный размер файла "' . $value['name'][$i] . '": ' . $formatBytes($maxSize));
                        continue;
                    }
                }
                if ($fileTypes = $currentParam['fileTypes']) {
                    if (!in_array($fileType, $fileTypes)) {
                        $this->setError('fileType', 'Недопустимый тип файла "' . $fileName . '" ');
                        continue;
                    }
                }
                $saveArr = [
                'name' => $fileName,
                'size' => $fileSize,
                'tmp_name' => $tmpName,
                'type' => $fileType
                ];
                $newValue[$i] = $saveArr;
            }
        }

        if ($this->errorCollection->isEmpty()) {
            if (method_exists($this, $param)) {
                $this->cleanParams[$param] = $this->$param($newValue);
            } else {
                $this->cleanParams[$param] = $newValue;
            }
        }
    }

    /**
     * Функция валидации полей
     *
     * TODO Разделить на методы
     *
     * @param array $params
     * @return self
     */
    public function validation(): self
    {
        if (!$this->params) {
            $this->setError('noParams', 'There are no fields');
        } else {
            $currentParamsErrors = false;
            foreach ($this->currentParams as $k => $v) {
                if ($v['required'] === true) {
                    if (empty($this->params[$k])) {
                        $erMessage = $k;
                        if ($v['name']) {
                            $erMessage = $v['name'];
                        }
                        $this->setError(
                            $k,
                            'Обязательное поле "' . $erMessage . '"'
                        );
                        $currentParamsErrors = true;
                    }
                }
            }
            if ($currentParamsErrors === true) {
                return $this;
            }
            foreach ($this->params as $param => $value) {
                if (!$value || !$param) {
                    continue;
                }
                if (is_array($value)) {
                    $this->validateFileParams($param, $value);
                } else {
                    $this->validateBaseParams($param, $value);
                }
            }
        }
        return $this;
    }

    /**
     * Если валидация пройдена
     *
     * @return array
     */
    abstract protected function successAction(): array;

    /**
     * Выполнить главное действие
     *
     * @return void
     */
    public function action(): void
    {
        if ($this->errorCollection->isEmpty()) {
            $this->replyData = $this->successAction();
            if ($this->errorCollection->isEmpty()) {
                if (isset($this->mail['eventType'])) {
                    $this->sendMail();
                }
            }
        }
    }

    /**
     * Получить коллекцию ошибок
     *
     * @return ErrorCollection
     */
    public function getErrorCollection(): ErrorCollection
    {
        return $this->errorCollection;
    }

    /**
     * Получить массив ошибок
     *
     * @return array
     */
    public function getErrorArray(): array
    {
        $errors = [];

        foreach ($this->errorCollection->toArray() as $error) {
            $errors[] = [
                'code' => $error->getCode(),
                'message' =>  $error->getMessage()
            ];
        }
        return $errors;
    }

    /**
     * Отправить сообщение на почту
     *
     * @return void
     */
    protected function sendMail(): void
    {
        \CEvent::Send(
            $this->mail['eventType'],
            SITE_ID,
            $this->cleanParams,
            'N',
            $this->mail['mailTemplateId'],
            $this->mailAttachments
        );
    }

    /**
     * Получить успешный ответ
     *
     * @return array
     */
    public function getReplyData(): array
    {
        return $this->replyData;
    }

    /**
     * Получить параметры формы
     *
     * @return array
     */
    public function getCurrentParams(): array
    {
        return $this->currentParams;
    }

    protected function preFilters(): array
    {
        return [
            new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
            new ActionFilter\Csrf()
        ];
    }

    protected function postFilters(): array
    {
        return [];
    }

    /**
     * Get the value of preFilters
     */
    public function getPreFilters(): array
    {
        return $this->preFilters;
    }

    /**
     * Get the value of postFilters
     */
    public function getPostFilters(): array
    {
        return $this->postFilters;
    }

    /**
     * Get the value of queryType
     */
    public function getQueryType(): string
    {
        return $this->queryType;
    }
}
