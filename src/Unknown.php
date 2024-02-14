<?php

namespace AidSoul\Bitrix\Form;
/**
 * Unknown class
 *
 * @author AidSoul <work-aidsoul@outlook.com>
 */
class Unknown extends Form
{
    protected function postFilters(): array{
        
    }
    public function successAction(): array
    {
        $this->setError('form', 'Form error');

        return [];
    }
}
