<?php

namespace AidSoul\Bitrix\Form;
/**
 * Unknown class
 *
 * @author AidSoul <work-aidsoul@outlook.com>
 */
class Unknown extends Form
{
    public function successAction(): array
    {
        $this->setError('form', 'Form error');

        return [];
    }
}
