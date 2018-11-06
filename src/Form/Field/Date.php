<?php

namespace Swoft\Admin\Form\Field;

class Date extends Text
{
    protected static $css = [
        '@admin/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css',
    ];

    protected static $js = [
        '@admin/moment/min/moment-with-locales.min.js',
        '@admin/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
    ];

    protected $format = 'YYYY-MM-DD';

    public function format($format)
    {
        $this->format = $format;

        return $this;
    }

    public function prepare($value)
    {
        if ($value === '') {
            $value = null;
        }

        return $value;
    }

    public function render()
    {
        $this->options['format'] = $this->format;
        $this->options['locale'] = current_lang();
        $this->options['allowInputToggle'] = true;

        $this->script = "$('{$this->getElementClassSelector()}').parent().datetimepicker(".json_encode($this->options).');';

        $this->prepend('<i class="fa fa-calendar fa-fw"></i>');

        return parent::render();
    }
}
