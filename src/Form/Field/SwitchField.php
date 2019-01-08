<?php

namespace Swoft\Admin\Form\Field;

use Swoft\Admin\Admin;
use Swoft\Admin\Form\Field;

class SwitchField extends Field
{
    protected static $css = [
        '@admin/switchery/switchery.min.css'
    ];

    protected static $js = [
        '@admin/switchery/switchery.min.js'
    ];

    public function primary()
    {
        return $this->attribute('data-color', '#0072C6');
    }

    public function green()
    {
        return $this->attribute('data-color', '#00b19d');
    }


    public function info()
    {
        return $this->attribute('data-color', '#3bafda');
    }

    public function warning()
    {
        return $this->attribute('data-color', '#ffaa00');
    }

    public function inverse()
    {
        return $this->attribute('data-color', '#4c5667');
    }

    public function danger()
    {
        return $this->attribute('data-color', '#ef5350');
    }

    public function purple()
    {
        return $this->attribute('data-color', '#5b69bc');
    }

    /**
     *
     * @param $color
     * @return $this
     */
    public function secondary($color)
    {
        return $this->attribute('data-secondary-color', $color);
    }

    /**
     * @return $this
     */
    public function small()
    {
        return $this->attribute('data-size', 'small');
    }

    /**
     * @return $this
     */
    public function large()
    {
        return $this->attribute('data-size', 'large');
    }

    /**
     * @param $color
     * @return $this
     */
    public function color($color)
    {
        return $this->attribute('data-color', $color);
    }

    /**
     * @param mixed $value
     * @return int
     */
    public function prepare($value)
    {
        return $value ? 1 : 0;
    }

    public function render()
    {
        if (empty($this->attributes['data-size'])) {
            $this->small();
        }
        if (empty($this->attributes['data-color'])) {
            $this->primary();
        }

        $this->attribute('name', $this->column);
        $this->attribute('value', 1);
        $this->attribute('type', 'checkbox');
        $this->attribute('data-plugin', 'switchery');

        Admin::script(<<<EOF
function swty(){\$('[data-plugin="switchery"]').each(function(){new Switchery($(this)[0],$(this).data())})} swty();
EOF
        );

        return parent::render();
    }
}
