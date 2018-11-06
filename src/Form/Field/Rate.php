<?php

namespace Swoft\Admin\Form\Field;

use Swoft\Admin\Form;

class Rate extends Text
{
    public function render()
    {
        $this->prepend('%')
                ->defaultAttribute('placeholder', 0);

        return parent::render();
    }
}
