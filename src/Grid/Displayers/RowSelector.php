<?php

namespace Swoft\Admin\Grid\Displayers;

use Swoft\Admin\Admin;
use Swoft\Support\Contracts\Htmlable;
use Swoft\Support\Contracts\Jsonable;
use Swoft\Support\Contracts\Renderable;

class RowSelector extends AbstractDisplayer
{
    public function display()
    {
        Admin::script($this->script());

        return <<<EOT
<input type="checkbox" class="{$this->grid->getGridRowName()}-checkbox" data-id="{$this->getPrimaryKey()}"  data-label="{$this->getLabel()}"/>
EOT;
    }

    /**
     * @return string
     */
    protected function getLabel()
    {
        if ($column = $this->grid->rowSelectorTextColumn) {
            return $this->row->{$column};
        }

        $label = $this->row->name ?: $this->row->title;
        if (!$label) {
            $label = $this->row->username ?: $this->row->user;
        }

        return $label ?: $this->getPrimaryKey();
    }


    protected function script()
    {
        return <<<EOT
$('.{$this->grid->getGridRowName()}-checkbox').iCheck({checkboxClass:'icheckbox_minimal-blue'}).on('ifChanged', function () {
    if (this.checked) {
        $(this).closest('tr').css('background-color', '#ffffd5');
    } else {
        $(this).closest('tr').css('background-color', '');
    }
});

window['{$this->grid->getSelectedRowsName()}'] = function () {
    var selected = [];
    $('.{$this->grid->getGridRowName()}-checkbox:checked').each(function(){
        selected.push($(this).data('id'));
    });
    return selected;
}
window['{$this->grid->getSelectedRowsName()}Options'] = function () {
        var selected = [];
        $('.{$this->grid->getGridRowName()}-checkbox:checked').each(function(){
            selected.push({'id': $(this).data('id'), 'label': $(this).data('label')})
        });

        return selected;
    };
EOT;
    }
}
