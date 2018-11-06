<?php

namespace Swoft\Admin\Form\Field;

class Icon extends Text
{
    protected $default = 'fa-pencil';

    protected static $css = [
        '@admin/fontawesome-iconpicker/dist/css/fontawesome-iconpicker.min.css',
    ];

    protected static $js = [
        '@admin/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.min.js',
    ];

    public function render()
    {
        $this->script = "$('{$this->getElementClassSelector()}').iconpicker({placement:'bottomLeft'});";

        $this->prepend('<i class="fa fa-pencil fa-fw"></i>');

        return parent::render();
    }
}
