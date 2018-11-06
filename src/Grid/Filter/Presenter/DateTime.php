<?php

namespace Swoft\Admin\Grid\Filter\Presenter;

use Swoft\Admin\Admin;

class DateTime extends Presenter
{
    public static $css = [
        '@admin/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css',
    ];

    public static $js = [
        '@admin/moment/min/moment-with-locales.min.js',
        '@admin/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
    ];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $format = 'YYYY-MM-DD HH:mm:ss';

    /**
     * DateTime constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->options = $this->getOptions($options);
    }

    /**
     * @param array $options
     *
     * @return mixed
     */
    protected function getOptions(array  $options) : array
    {
        $options['format'] = array_get($options, 'format', $this->format);
        $options['locale'] = array_get($options, 'locale', current_lang());

        return $options;
    }

    protected function prepare()
    {
        $script = "$('#{$this->filter->getId()}').datetimepicker(".json_encode($this->options).');';

        Admin::script($script);
    }

    public function variables() : array
    {
        $this->prepare();

        return [];
    }
}
