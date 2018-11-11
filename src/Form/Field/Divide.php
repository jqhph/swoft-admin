<?php

namespace Swoft\Admin\Form\Field;

use Swoft\Admin\Form;
use Swoft\Admin\Form\Field;

class Divide extends Field
{
    public function render()
    {
        $style = '';
        if ($this->style === Form::STYLE_ROW) {
            $style = 'style="margin-bottom:0;margin-top:10px"';
        }

        return "<hr $style/>";
    }
}
