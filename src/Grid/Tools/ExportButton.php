<?php

namespace Swoft\Admin\Grid\Tools;

use Swoft\Admin\Admin;
use Swoft\Admin\Grid;
use Swoft\Core\RequestContext;

class ExportButton extends AbstractTool
{
    /**
     * @var Grid
     */
    protected $grid;

    /**
     * Create a new Export button instance.
     *
     * @param Grid $grid
     */
    public function __construct(Grid $grid)
    {
        $this->grid = $grid;
    }

    /**
     * Set up script for export button.
     */
    protected function setUpScripts()
    {
        $script = <<<SCRIPT
$('.{$this->grid->getExportSelectedName()}').click(function (e) {
    e.preventDefault();
    var rows = {$this->grid->getSelectedRowsName()}().join(',');
    if (!rows) {
        return false;
    }
    
    var href = $(this).attr('href').replace('__rows__', rows);
    location.href = href;
});
SCRIPT;

        Admin::script($script);
    }

    /**
     * Render Export button.
     *
     * @return string
     */
    public function render()
    {
        if (!$this->grid->allowExport()) {
            return '';
        }

        $this->setUpScripts();

        $export       = t('Export', 'admin');
        $all          = t('All', 'admin');
        $currentPage  = t('Current Page', 'admin');
        $selectedRows = t('Selected Rows', 'admin');

        $page = (int)RequestContext::getRequest()->query('page', 1);

        return <<<EOT
<div class="btn-group" >
    <button type="button" class="btn btn-custom btn-sm dropdown-toggle" data-toggle="dropdown">
    <i class="fa fa-download"></i><span class="hidden-xs">&nbsp; {$export} &nbsp;</span>
        <span class="caret"></span>
        <span class="sr-only"></span>
    </button>
    <ul class="dropdown-menu" role="menu">
        <li><a href="{$this->grid->getExportUrl('all')}" target="_blank">{$all}</a></li>
        <li><a href="{$this->grid->getExportUrl('page', $page)}" target="_blank">{$currentPage}</a></li>
        <li><a href="{$this->grid->getExportUrl('selected', '__rows__')}" target="_blank" class='{$this->grid->getExportSelectedName()}'>{$selectedRows}</a></li>
    </ul>
</div>
EOT;
    }
}
