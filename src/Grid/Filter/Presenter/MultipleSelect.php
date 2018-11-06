<?php

namespace Swoft\Admin\Grid\Filter\Presenter;

class MultipleSelect extends Select
{
    /**
     * @var int
     */
    protected $width = 4;

    /**
     * @var bool
     */
    protected $clear = true;

    protected function setupScript()
    {
        $this->onReset(
            <<<EOF
    $('.{$this->getElementClass()}').select2('val', ' ');
EOF
        );
    }
}
