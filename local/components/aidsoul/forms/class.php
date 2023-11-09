<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
use AidSoul\Forms\Form;
use AidSoul\Forms\Unknown;
use Bitrix\Main\Errorable;
use Bitrix\Main\Application;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\Engine\Contract\Controllerable;

/**
 * Forms
 * @author AidSoul <work-aidsoul@outlook.com>
 */
class Forms extends CBitrixComponent implements Controllerable, Errorable
{
    use ErrorableImplementation;

    private string $formCode = '';

    /**
     * SECTION ID
     */
    public const IBLOCK_ID = 0;

    /**
     * Классы для работы с формами
     *
     * @param string $form
     * @return Form
     */
    private function getFormClass(string $form): Form
    {
        return match ($form) {
            // form init
            default => new Unknown()
        };
    }

    public function onPrepareComponentParams($arParams)
    {
        $arParams['CACHE_TIME'] = isset($arParams['CACHE_TIME']) ? $arParams['CACHE_TIME'] : 86400;
        return $arParams;
    }

    public function configureActions(): array
    {
        return [
            'ajax' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf()
                ],
                'postfilters' => []
            ],
        ];
    }

    /**
     * Работа с ajax
     */
    public function ajaxAction()
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $params = $request->getPostList()->toArray();
        $form = $this->getFormClass($params['form']);
        unset($params['form']);
        if ($files = $request->getFileList()->toArray()) {
            $params = array_merge($params, $files);
        }
        $form->validation($params);
        $form->prepareAction();
        $this->errorCollection = $form->getErrors();
        if (empty($this->getErrors())) {
            $form->successAction();
            $form->showSuccessErrors();
            $form->sendMail();
            return $form->getSuccessResponse();
        }
    }

    public function executeComponent()
    {

        // Asset::getInstance()->addJs($this->getPath() . '/forms.js');
        /**
         * Формирования массива для формы
         */
        $form = $this->getFormClass($this->getTemplateName());
        $form->setFormData($this->arParams['FORM'] ?? []);
        $this->arResult['DATA'] = $form->formDataAction();
        $this->arResult['CLASS'] = $this->arParams['CLASS'];
        // Авторизован ли пользователь
        // if ($this->arParams['IS_USER_AUTHORIZED'] === true) {
        // checkUserAndForward('/account/');
        // }
        if ($this->startResultCache()) {
            $this->includeComponentTemplate();
        }
    }
}
