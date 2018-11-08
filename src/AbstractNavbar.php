<?php

namespace Swoft\Admin;

use Swoft\Admin\Widgets\Navbar;

/**
 * 顶部导航栏定义
 */
abstract class AbstractNavbar
{
    /**
     * 顶部导航栏模板文件
     *
     * @var string
     */
    protected $view;

    /**
     * 自定义顶部导航栏内容
     *
     * @param Navbar $navbar
     * @return void
     */
    abstract public function build(Navbar $navbar);

    /**
     * 获取导航栏模板文件
     *
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }
}
