<?php

namespace Swoft\Admin\Form\Field;

class Ip extends Text
{
    protected $rules = 'ip';

    protected static $js = [
        '@admin/AdminLTE/plugins/input-mask/jquery.inputmask.bundle.min.js',
    ];

    /**
     * @see https://github.com/RobinHerbots/Inputmask#options
     *
     * @var array
     */
    protected $options = [
        'alias' => 'ip',
    ];

    public function render()
    {
        $options = json_encode($this->options);

        $this->script = <<<EOT

$('{$this->getElementClassSelector()}').inputmask($options);
EOT;

        $this->prepend('<i class="fa fa-laptop fa-fw"></i>');
//            ->defaultAttribute('style', 'width:270px');

        return parent::render();
    }
}
