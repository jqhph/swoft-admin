<?php

namespace Swoft\Admin\Form\Field;

class Decimal extends Text
{
    protected static $js = [
        '@admin/AdminLTE/plugins/input-mask/jquery.inputmask.bundle.min.js',
    ];

    /**
     * @see https://github.com/RobinHerbots/Inputmask#options
     *
     * @var array
     */
    protected $options = [
        'alias'      => 'decimal',
        'rightAlign' => true,
    ];

    public function render()
    {
        $options = json_encode($this->options);

        $this->script = "$('{$this->getElementClassSelector()}').inputmask($options);";

        $this->prepend('<i class="fa fa-terminal fa-fw"></i>')
            ->defaultAttribute('style', 'width:270px');

        return parent::render();
    }
}
