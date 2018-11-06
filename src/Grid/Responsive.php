<?php

namespace Swoft\Admin\Grid;

use Swoft\Admin\Admin;
use Swoft\Admin\Grid;

/**
 * @see http://gergeo.se/RWD-Table-Patterns/#demo
 */
class Responsive
{
    /**
     * @var Grid
     */
    protected $grid;

    /**
     * @var array
     */
    protected $options = [];

    public function __construct(Grid $grid)
    {
        $this->grid = $grid;
        $this->option([
            'i18n' => [
                'focus' => t('Focus', 'admin.responsive'),
                'display' => t('Display', 'admin.responsive'),
                'displayAll' => t('Display all', 'admin.responsive'),
            ],
        ]);
    }

    public function disableFocus()
    {
        return $this->option('addFocusBtn', false);
    }

    /**
     * 配置
     *
     * @param $key
     * @param null $value
     * @return $this
     */
    public function option($key, $value = null)
    {
        if (!$key) {
            return $this;
        }
        if (is_array($key)) {
            $this->options = array_merge($this->options, $key);
            return $this;
        }
        $this->options[$key] = $value;
        return $this;
    }

    public function build()
    {
        Admin::css('@admin/RWD-Table-Patterns/dist/css/rwd-table.min.css');
        Admin::js('@admin/RWD-Table-Patterns/dist/js/rwd-table.min.js');

        $opt = json_encode($this->options);

        Admin::script("$('.table-responsive').responsiveTable($opt);");

        return '';
    }

}
