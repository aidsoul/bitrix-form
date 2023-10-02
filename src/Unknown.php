<?php

namespace AidSoul\Bitrix\Form;

class Unknown extends Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setError('form', 'Form error');
    }
}
