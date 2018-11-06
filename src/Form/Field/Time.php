<?php

namespace Swoft\Admin\Form\Field;

class Time extends Date
{
    protected $format = 'HH:mm:ss';

    public function render()
    {
        $this->prepend('<i class="fa fa-clock-o fa-fw"></i>');

        return parent::render();
    }
}
