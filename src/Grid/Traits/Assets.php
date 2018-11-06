<?php

namespace Swoft\Admin\Grid\Traits;

use Swoft\Admin\Admin;

trait Assets
{
    /**
     * 收集静态资源
     */
    protected function collectAssets()
    {
        $this->collectRowSelectorAssets();
        $this->collectFiltersAssets();
    }

    /**
     * 收集过滤器静态资源
     *
     * @return void
     */
    protected function collectFiltersAssets()
    {
        $js = $css = [];

        foreach ($this->filter->getLayout()->columns() as $column) {
            foreach ($column->filters() as $filter) {
                $cls = get_class($filter->presenter());

                $js  = array_merge($js, $cls::$js);
                $css = array_merge($css, $cls::$css);
            }
        }

        if ($js) {
            Admin::js(array_unique($js));
        }
        if ($css) {
            Admin::css(array_unique($css));
        }
    }

    /**
     * 静态资源加载
     */
    protected function collectRowSelectorAssets()
    {
        if ($this->options['useRowSelector']) {
            Admin::css('@admin/AdminLTE/plugins/iCheck/minimal/_all.css');
            Admin::js('@admin/AdminLTE/plugins/iCheck/icheck.min.js');
        }
    }
}
