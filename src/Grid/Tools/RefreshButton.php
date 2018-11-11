<?php

namespace Swoft\Admin\Grid\Tools;

use Swoft\Admin\Admin;
use Swoft\Support\Url;

class RefreshButton extends AbstractTool
{
    /**
     * Script for this tool.
     *
     * @return string
     */
    protected function script()
    {
        $message = t('Refresh succeeded!', 'admin');
        $url = Url::current();
        return <<<EOT
$('.grid-refresh').on('click', function() {
    $.pjax.reload({container:'#pjax-container', url: '$url'});
    setTimeout(function () {LA.success('{$message}');},100);
});
EOT;
    }

    /**
     * Render refresh button of grid.
     *
     * @return string
     */
    public function render()
    {
        Admin::script($this->script());

        $refresh = t('Refresh', 'admin');

        return <<<EOT
&nbsp;<a class=" btn btn-info grid-refresh" ><i class="fa fa-refresh"></i><span class="hidden-xs"> $refresh</span></a>&nbsp;
EOT;
    }
}
