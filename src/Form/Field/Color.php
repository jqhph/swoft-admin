<?php

namespace Swoft\Admin\Form\Field;

class Color extends Text
{
    protected static $css = [
        '@admin/AdminLTE/plugins/colorpicker/bootstrap-colorpicker.min.css',
    ];

    protected static $js = [
        '@admin/AdminLTE/plugins/colorpicker/bootstrap-colorpicker.min.js',
    ];

    /**
     * Use `hex` format.
     *
     * @return $this
     */
    public function hex()
    {
        return $this->options(['format' => 'hex']);
    }

    /**
     * Use `rgb` format.
     *
     * @return $this
     */
    public function rgb()
    {
        return $this->options(['format' => 'rgb']);
    }

    /**
     * Use `rgba` format.
     *
     * @return $this
     */
    public function rgba()
    {
        return $this->options(['format' => 'rgba']);
    }

    /**
     * Render this filed.
     *
     * @return string
     */
    public function render()
    {
        $options = json_encode($this->options);

        if (!$this->style) {
            $this->script = "$('{$this->getElementClassSelector()}').parent().colorpicker($options);";

            $this->prepend('<i></i>');
        } else {
            $this->script = "$('{$this->getElementClassSelector()}').colorpicker($options);";

            $this->prepend('<i style="background:#222;width:11px;height:11px;display:inline-block"></i>');
        }

        return parent::render();
    }
}
