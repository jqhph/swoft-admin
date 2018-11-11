<?php

namespace Swoft\Admin\Grid\Displayers;

use Swoft\Admin\Admin;

class Checkbox extends AbstractDisplayer
{
    /**
     * @var string|array
     */
    protected $options = [];

    /**
     *
     * @param string|array $options
     * @return $this
     */
    public function options($options)
    {
        $this->options = $options;
        return $this;
    }

    public function display($options = null)
    {
        if ($options) {
            $this->options($options);
        }

        $radios = '';
        $name = $this->column->getName();

        if (is_string($this->value)) {
            $this->value = explode(',', $this->value);
        }

        foreach ($this->options as $value => $label) {
            $checked = in_array($value, $this->value) ? 'checked' : '';
            $radios .= <<<EOT
<div class="checkbox">
    <label>
        <input type="checkbox" name="grid-checkbox-{$name}[]" value="{$value}" $checked />{$label}
    </label>
</div>
EOT;
        }

        Admin::script($this->script());

        $save = t('Save', 'admin');
        $reset = t('Reset', 'admin');


        return <<<EOT
<form class="form-group grid-checkbox-$name" style="text-align:left;" data-key="{$this->getPrimaryKey()}">
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
$('form.grid-checkbox-$name').on('submit', function () {
    var values = $(this).find('input:checkbox:checked').map(function (_, el) {
        return $(el).val();
    }).get();
    var url = '{$url}';

    var data = {
        name: '$name',
        value: values,
        _token: LA.token,
        _editable: 1,
        _method: 'PUT'
    };
    
    $.ajax({
        url: url.replace(':id', $(this).data('key')),
        type: "POST",
        contentType: 'application/json;charset=utf-8',
        data: JSON.stringify(data),
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
