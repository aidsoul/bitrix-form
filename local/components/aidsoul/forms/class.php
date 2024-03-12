<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use AidSoul\Forms\Form;
use AidSoul\Forms\Unknown;

/**
 * Forms
 * @author AidSoul <work-aidsoul@outlook.com>
 */
class Forms extends CBitrixComponent implements Controllerable, Errorable
{
    use ErrorableImplementation;

    private string $formCode = '';
    private string $formName = '';
    private array $configureArr;
    private Form $form;

    public function onPrepareComponentParams($arParams)
    {
        $arParams['CACHE_TIME'] = isset($arParams['CACHE_TIME']) ? $arParams['CACHE_TIME'] : 86400;
        $form = $this->request->getPost('form') ?? 'no';
        $this->form = $this->getFormClass($form);
        if ($preFilters = $this->form->getPreFilters()) {
            $this->configureArr['prefilters'] = $preFilters;
        }
        if ($postFilters = $this->form->getPostFilters()) {
            $this->configureArr['postfilters'] = $postFilters;
        }
        return $arParams;
    }

    public function configureActions(): array
    {
        return [
            'ajax' => $this->configureArr
        ];
    }

    /**
     * Классы для работы с формами
     *
     * @param string $form
     * @return Form
     */
    private function getFormClass(string $form): Form
    {
        return match ($form) {
            default => new Unknown()
        };
    }

    private function getReplyAction(): array
    {
        /**
         * @var $request \Bitrix\Main\HttpRequest
         */
        $form = &$this->form;
        $request = $this->request;
        $params = [];
        switch ($form->getQueryType()) {
            case 'get':
                $params = $request->getQueryList()->toArray();
                break;
            case 'post':
                $params = $request->getPostList()->toArray();
                if ($files = $request->getFileList()->toArray()) {
                    $params = array_merge($params, $files);
                }
                break;
            case 'all':
                $params = array_merge(
                    $request->getQueryList()->toArray(),
                    $request->getPostList()->toArray(),
                    $request->getFileList()->toArray()
                );
                break;
        }
        if ($form === 0) {
            $form = $this->formName;
        }
        unset($params['form']);
        $form
        ->setParams($params)
        ->validation()
        ->action();
        $this->errorCollection = $form->getErrorCollection();
        return $form->getReplyData();
    }
    public function setFormName(string $name): void
    {
        $this->formName = $name;
    }


    /**
     * Работа с ajax
     */
    public function ajaxAction()
    {
        return $this->getReplyAction();
    }

    public function executeComponent()
    {

        $form = $this->getFormClass($this->getTemplateName());
        $form->setArParams($this->arParams['FORM'] ?? []);
        $this->arResult = $form->getArResult();
        $this->arResult['CURRENT_PARAMS'] = $form->getCurrentParams();

        if ($this->startResultCache()) {
            $this->includeComponentTemplate();
        }
    }
}
