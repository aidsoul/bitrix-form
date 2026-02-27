<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\Error;
use AidSoul\Bitrix\Form\Form;
use AidSoul\Bitrix\Form\Unknown;

/**
 * Forms Component
 * 
 * Bitrix component for handling AJAX forms with validation
 * 
 * @author AidSoul <work-aidsoul@outlook.com>
 */
class Forms extends CBitrixComponent implements Controllerable, Errorable
{
    use ErrorableImplementation;

    /** @var string */
    private string $formCode = '';
    
    /** @var string */
    private string $formName = '';
    
    /** @var array */
    private array $configureArr = [];
    
    /** @var Form */
    private Form $form;

    /**
     * Register available form classes
     * Override this method to add your own forms
     * 
     * @return array Form classes configuration
     */
    public static function registerForms(): array
    {
        return [
            // Example: 'contact' => ContactForm::class,
            // 'callback' => CallbackForm::class,
        ];
    }

    /**
     * Prepare component parameters
     * 
     * @param array $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams): array
    {
        $arParams['CACHE_TIME'] = isset($arParams['CACHE_TIME']) ? (int)$arParams['CACHE_TIME'] : 86400;
        
        // Get form name from request or parameters
        $form = $this->request->getPost('form') 
            ?? $this->request->getQuery('form') 
            ?? $arParams['FORM_NAME'] 
            ?? 'default';
        
        $this->form = $this->getFormClass($form);
        
        if ($preFilters = $this->form->getPreFilters()) {
            $this->configureArr['prefilters'] = $preFilters;
        }
        
        if ($postFilters = $this->form->getPostFilters()) {
            $this->configureArr['postfilters'] = $postFilters;
        }

        // Set form parameters
        $this->form->setArParams($arParams['FORM'] ?? []);
        
        return $arParams;
    }

    /**
     * Configure AJAX actions
     * 
     * @return array
     */
    public function configureActions(): array
    {
        return [
            'ajax' => $this->configureArr
        ];
    }

    /**
     * Get form class by form name
     * 
     * @param string $form
     * @return Form
     */
    private function getFormClass(string $form): Form
    {
        $registeredForms = static::registerForms();
        
        if (isset($registeredForms[$form])) {
            $formClass = $registeredForms[$form];
            if (class_exists($formClass)) {
                return new $formClass();
            }
        }
        
        // Return default form class
        return new Unknown();
    }

    /**
     * Process AJAX request
     * 
     * @return array
     */
    private function getReplyAction(): array
    {
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
        
        // Remove form identifier from params
        unset($params['form']);
        
        // Process form
        $form->setParams($params);
        
        // Validate
        $form->validation();
        
        // Execute action
        $form->action();
        
        // Copy errors to component
        $this->errorCollection = $form->getErrorCollection();
        
        return $form->getReplyData();
    }

    /**
     * Set form name programmatically
     * 
     * @param string $name
     * @return void
     */
    public function setFormName(string $name): void
    {
        $this->formName = $name;
    }

    /**
     * AJAX action handler
     * 
     * @return array
     */
    public function ajaxAction(): array
    {
        return $this->getReplyAction();
    }

    /**
     * Get form fields action (for AJAX form initialization)
     * 
     * @return array
     */
    public function getFieldsAction(): array
    {
        return [
            'fields' => $this->form->getCurrentParams(),
            'formData' => $this->form->getArResult()
        ];
    }

    /**
     * Execute component
     */
    public function executeComponent()
    {
        // Get form from template name
        $formName = $this->getTemplateName() ?: 'default';
        $form = $this->getFormClass($formName);
        $form->setArParams($this->arParams['FORM'] ?? []);
        
        // Prepare result
        $this->arResult = $form->getArResult();
        $this->arResult['CURRENT_PARAMS'] = $form->getCurrentParams();
        
        // Add form configuration
        $this->arResult['FORM'] = [
            'NAME' => $formName,
            'ID' => $this->arParams['FORM_ID'] ?? 'form-' . $formName,
            'CLASS' => $this->arParams['FORM_CLASS'] ?? '',
            'AJAX' => $this->arParams['AJAX'] ?? true,
            'ACTION' => $this->arParams['ACTION'] ?? '',
            'TITLE' => $this->arParams['TITLE'] ?? '',
            'SUBMIT_TEXT' => $this->arParams['SUBMIT_TEXT'] ?? 'Отправить',
            'POLITIC_URL' => $this->arParams['POLITIC_URL'] ?? ''
        ];

        // Cache and include template
        if ($this->startResultCache()) {
            $this->includeComponentTemplate();
        }
    }
}
