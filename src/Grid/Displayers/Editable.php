<?php

namespace Swoft\Admin\Grid\Displayers;

use Swoft\Admin\Admin;

class Editable extends AbstractDisplayer
{
    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * Type of editable.
     *
     * @var string
     */
    protected $type = '';

    /**
     * Options of editable function.
     *
     * @var array
     */
    protected $options = [
        'emptytext'  => '<i class="fa fa-pencil"></i>',
    ];

    /**
     * Add options for editable.
     *
     * @param array $options
     */
    public function addOptions($options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Text type editable.
     */
    public function text()
    {
    }

    /**
     * Textarea type editable.
     */
    public function textarea()
    {
    }

    /**
     * Select type editable.
     *
     * @param array $options
     */
    public function select($options = [])
    {
        $this->type = 'select';

        $source = [];

        foreach ($options as $key => $value) {
            $source[] = [
                'value' => $key,
                'text'  => $value,
            ];
        }

        $this->addOptions(['source' => $source]);
    }

    /**
     * Date type editable.
     */
    public function date()
    {
        $this->combodate();
    }

    /**
     * Datetime type editable.
     */
    public function datetime()
    {
        $this->combodate('YYYY-MM-DD HH:mm:ss');
    }

    /**
     * Year type editable.
     */
    public function year()
    {
        $this->combodate('YYYY');
    }

    /**
     * Month type editable.
     */
    public function month()
    {
        $this->combodate('MM');
    }

    /**
     * Day type editable.
     */
    public function day()
    {
        $this->combodate('DD');
    }

    /**
     * Combodate type editable.
     *
     * @param string $format
     */
    public function combodate($format = 'YYYY-MM-DD')
    {
        $this->type = 'combodate';

        $this->addOptions([
            'format'     => $format,
            'viewformat' => $format,
            'template'   => $format,
            'combodate'  => [
                'maxYear' => 2035,
            ],
        ]);

        Admin::js('@admin/moment/min/moment-with-locales.min.js');
    }

    protected function buildEditableOptions(array $arguments = [])
    {
        if ($this->type) {
            return;
        }
        $this->type = array_get($arguments, 0, 'text');

        call_user_func_array([$this, $this->type], array_slice($arguments, 1));
    }

    public function display()
    {
        Admin::js([
            '@admin/bootstrap3-editable/js/bootstrap-editable.min.js'
        ]);
        Admin::css([
            '@admin/bootstrap3-editable/css/bootstrap-editable.css'
        ]);
        Admin::script(<<<EOF
$.fn.editable.defaults.params = function (params) {
    params._token = LA.token;
    params._editable = 1;
    params._method = 'PUT';
    return params;
};
$.fn.editable.defaults.error = function (data) {
    var msg = '';
    if (data.responseJSON.errors) {
        $.each(data.responseJSON.errors, function (k, v) {
            msg += v + "\\n";
        });
    }
    return msg
};
EOF
);

        $this->options['name'] = $column = $this->column->getName();

        $class = 'grid-editable-'.str_replace(['.', '#', '[', ']'], '-', $column);

        $this->buildEditableOptions(func_get_args());

        $options = json_encode($this->options);

        Admin::script("$('.$class').editable($options);");

        $url = Admin::url()->updateField($this->getPrimaryKey());

        $attributes = [
            'href'       => '#',
            'class'      => "$class",
            'data-type'  => $this->type,
            'data-pk'    => "{$this->getPrimaryKey()}",
            'data-url'   => $url,
            'data-value' => "{$this->value}",
        ];

        $attributes = collect($attributes)->map(function ($attribute, $name) {
            return "$name='$attribute'";
        })->implode(' ');

        $html = $this->type === 'select' ? '' : $this->value;

        return "<a $attributes>{$html}</a>";
    }
}
