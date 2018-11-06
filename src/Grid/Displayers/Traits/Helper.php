<?php

namespace Swoft\Admin\Grid\Displayers\Traits;

use Swoft\Admin\Grid\Column;

trait Helper
{
    /**
     * 分割字符串
     *
     * @param string $d
     * @return Column
     */
    public function explode(string $d = ',')
    {
        return $this->display(function ($value) use ($d) {
            return explode($d, $value);
        });
    }

    /**
     * 转化为数组
     *
     * @return Column
     */
    public function jsonDecode()
    {
        return $this->display(function ($value) {
            return json_decode($value);
        });
    }

}
