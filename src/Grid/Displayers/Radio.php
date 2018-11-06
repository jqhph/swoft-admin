<?php

namespace Swoft\Admin\Grid\Displayers;

use Swoft\Admin\Admin;

class Radio extends AbstractDisplayer
{
    public function display($options = [])
    {
        $radios = '';
        $name = $this->column->getName();

        foreach ($options as $value => $label) {
            $checked = ($value == $this->value) ? 'checked' : '';
            $radios .= <<<EOT
<div class="radio">
    <label>
        <input type="radio" name="grid-radio-$name" value="{$value}" $checked />{$label}
    </label>
</div>
EOT;
        }

        Admin::script($this->script());

        $save = t('Save', 'admin');
        $reset = t('Reset', 'admin');

        return <<<EOT
<form class="form-group grid-radio-$name" style="text-align: left" data-key="{$this->getPrimaryKey()}">
    $radios
    <button type="submit" class="btn btn-info btn-xs pull-left">
        <i class="fa fa-save"></i>&nbsp;{$save}
    </button>
    <button type="reset" class="btn btn-warning btn-xs pull-left" style="margin-left:10px;">
        <i class="fa fa-undo"></i>&nbsp;{$reset}
    </button>
</form>
EOT;
    }

    protected function script()
    {
        $name = $this->column->getName();

        $url = Admin::url()->updateField();

        return <<<EOT
$('form.grid-radio-$name').on('submit', function () {
    var value = $(this).find('input:radio:checked').val();
    var url = '{$url}';
    $.ajax({
        url: url.replace(':id', $(this).data('key')),
        type: "POST",
        data: {
             name: '$name',
             value: value,
            _token: LA.token,
            _editable: 1,
            _method: 'PUT'
        },
        success: function (data) {
            if (data.errors) {
                return LA.error(data.errors.join("\\n"));
            }
            if (data.status) {
                LA.success(data.message);
            } else {
                LA.error(data.message);
            }
        }
    });

    return false;
});
EOT;
    }
}
