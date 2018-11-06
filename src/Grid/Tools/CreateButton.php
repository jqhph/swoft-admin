<?php

namespace Swoft\Admin\Grid\Tools;

use Swoft\Admin\Admin;
use Swoft\Admin\Grid;

class CreateButton extends AbstractTool
{
    /**
     * @var Grid
     */
    protected $grid;

    /**
     * Create a new CreateButton instance.
     *
     * @param Grid $grid
     */
    public function __construct(Grid $grid)
    {
        $this->grid = $grid;
    }

    /**
     * Render CreateButton.
     *
     * @return string
     */
    public function render()
    {
        if (!$this->grid->allowCreation()) {
            return '';
        }

        $new = t('New', 'admin');
        $url = Admin::url()->create();

        return <<<EOT
<div class="btn-group" >
    <a href="{$url}" class="btn btn-success" title="{$new}">
        <i class="fa fa-save"></i><span class="hidden-xs">&nbsp;&nbsp;{$new}</span>
    </a>
</div>

EOT;
    }
}
