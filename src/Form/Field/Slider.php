<?php

namespace Swoft\Admin\Form\Field;

use Swoft\Admin\Form\Field;

class Slider extends Field
{
    protected static $css = [
        '@admin/AdminLTE/plugins/ionslider/ion.rangeSlider.css',
        '@admin/AdminLTE/plugins/ionslider/ion.rangeSlider.skinNice.css',
    ];

    protected static $js = [
        '@admin/AdminLTE/plugins/ionslider/ion.rangeSlider.min.js',
    ];

    protected $options = [
        'type'     => 'single',
        'prettify' => false,
        'hasGrid'  => true,
    ];

    public function render()
    {
        $option = json_encode($this->options);

        $this->script = "$('{$this->getElementClassSelector()}').ionRangeSlider($option)";

        return parent::render();
    }
}
