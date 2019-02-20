<?php

namespace Swoft\Admin\Grid\Filter\Presenter;

use Swoft\Admin\Grid\Filter;
use Swoft\Admin\Admin;
use Swoft\Contract\Arrayable;

class Select extends Presenter
{
    /**
     * @var array
     */
    public static $css = [
        '@admin/AdminLTE/plugins/select2/select2.min.css',
    ];

    /**
     * @var array
     */
    public static $js = [
        '@admin/AdminLTE/plugins/select2/select2.full.min.js',
    ];

    /**
     * @var int
     */
    protected $width = 2;

    /**
     * Options of select.
     *
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $script = '';

    /**
     * @var bool
     */
    protected $selectAll = true;

    /**
     * @var bool
     */
    protected $clear = false;

    /**
     * Select constructor.
     *
     * @param mixed $options
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * 初始化
     */
    protected function setupScript()
    {
        $ignore = Filter::$ignoreValue;
        $this->onReset(
            <<<EOF
    $('.{$this->getElementClass()}').val("$ignore").trigger("change");
EOF
        );
    }

    /**
     * 是否禁用“所有”选项
     *
     * @return $this
     */
    public function disableSelectAll()
    {
        $this->selectAll = false;
        return $this;
    }

    /**
     * Set config for select2.
     *
     * all configurations see https://select2.org/configuration/options-api
     *
     * @param string $key
     * @param mixed  $val
     *
     * @return $this
     */
    public function config($key, $val)
    {
        $this->config[$key] = $val;

        return $this;
    }

    /**
     * Build options.
     *
     * @return array
     */
    protected function buildOptions() : array
    {
        if (is_string($this->options)) {
            $this->loadRemoteOptions($this->options);
        }

        if ($this->options instanceof \Closure) {
            $this->options = $this->options->call($this->filter, $this->filter->getValue());
        }

        if ($this->options instanceof Arrayable) {
            $this->options = $this->options->toArray();
        }

        if (empty($this->script)) {
            $clear = $this->clear ? 'true' : 'false';

            $placeholder = t('Choose', 'admin');

            $this->script = <<<SCRIPT
$(".{$this->getElementClass()}").select2({
  placeholder: "$placeholder",allowClear:{$clear}
});
SCRIPT;
        }

        Admin::script($this->script);

        return is_array($this->options) ? $this->options : [];
    }

    /**
     * Load options from remote.
     *
     * @param string $url
     * @param array  $parameters
     * @param array  $options
     *
     * @return $this
     */
    protected function loadRemoteOptions($url, $parameters = [], $options = [])
    {
        $ajaxOptions = [
            'url' => $url.'?'.http_build_query($parameters),
        ];

        $ajaxOptions = json_encode(array_merge($ajaxOptions, $options));

        $this->script = <<<EOT
$.ajax($ajaxOptions).done(function(data) {
  $(".{$this->getElementClass()}").select2({data: data});
});
EOT;
    }

    /**
     * Load options from ajax.
     *
     * @param string $resourceUrl
     * @param $idField
     * @param $textField
     */
    public function ajax($resourceUrl, $idField = 'id', $textField = 'text')
    {
        $configs = array_merge([
            'allowClear'         => true,
            'placeholder'        => t('Choose', 'admin'),
            'minimumInputLength' => 1,
        ], $this->config);

        $configs = json_encode($configs);
        $configs = substr($configs, 1, strlen($configs) - 2);

        $this->script = <<<EOT
$(".{$this->getElementClass()}").select2({
  ajax: {
    url: "$resourceUrl",
    dataType: 'json',
    delay: 250,
    data: function (params) {
      return {
        q: params.term,
        page: params.page
      };
    },
    processResults: function (data, params) {
      params.page = params.page || 1;

      return {
        results: $.map(data.data, function (d) {
                   d.id = d.$idField;
                   d.text = d.$textField;
                   return d;
                }),
        pagination: {
          more: data.next_page_url
        }
      };
    },
    cache: true
  },
  $configs,
  escapeMarkup: function (markup) {
      return markup;
  }
});

EOT;
    }

    /**
     * @return array
     */
    public function variables() : array
    {
        $this->setupScript();
        return [
            'options'   => $this->buildOptions(),
            'class'     => $this->getElementClass(),
            'selectAll' => $this->selectAll
        ];
    }

    /**
     * @return string
     */
    protected function getElementClass() : string
    {
        return str_replace('.', '_', $this->filter->getColumn());
    }

    /**
     * Load options for other select when change.
     *
     * @param string $target
     * @param string $resourceUrl
     * @param string $idField
     * @param string $textField
     *
     * @return $this
     */
    public function load($target, $resourceUrl, $idField = 'id', $textField = 'text') : self
    {
        $column = $this->filter->getColumn();

        $script = <<<EOT
$(document).on('change', ".{$this->getClass($column)}", function () {
    var target = $(this).closest('form').find(".{$this->getClass($target)}");
    $.get("$resourceUrl?q="+this.value, function (data) {
        target.find("option").remove();
        $.each(data, function (i, item) {
            $(target).append($('<option>', {
                value: item.$idField,
                text : item.$textField
            }));
        });
        
        $(target).trigger('change');
    });
});
EOT;

        Admin::script($script);

        return $this;
    }

    /**
     * Get form element class.
     *
     * @param string $target
     *
     * @return mixed
     */
    protected function getClass($target) : string
    {
        return str_replace('.', '_', $target);
    }
}
