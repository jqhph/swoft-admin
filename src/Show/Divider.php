<?php

namespace Swoft\Admin\Show;

class Divider extends Field
{
    protected $width = 12;

    /**
     * @var bool
     */
    protected $showLine = false;

    public function __construct(bool $showLine = true)
    {
        $this->showLine = $showLine;
    }

    public function render()
    {
        $value = $this->showLine ? '<hr/>' : '<br/>';

        return <<<EOF
<div class="col-sm-{$this->width}">$value</div>
EOF;

    }
}
