<?php

namespace Swoft\Admin\Widgets;

use Swoft\Admin\AbstractNavbar;
use Swoft\Admin\Bean\Collector\AdminNavbarCollector;
use Swoft\Support\Contracts\Htmlable;
use Swoft\Support\Contracts\Renderable;

class Navbar implements Renderable
{
    /**
     * @var array
     */
    protected $elements = [];

    /**
     * @var string
     */
    protected $view = 'admin::partials.header';

    /**
     * Navbar constructor.
     */
    public function __construct()
    {
        $this->elements = [
            'left'  => collect(),
            'right' => collect(),
        ];
    }

    /**
     * @param $element
     *
     * @return $this
     */
    public function left($element)
    {
        $this->elements['left']->push(value($element));

        return $this;
    }

    /**
     * @param $element
     *
     * @return $this
     */
    public function right($element)
    {
        $this->elements['right']->push(value($element));

        return $this;
    }

    /**
     * @param $element
     *
     * @return Navbar
     *
     * @deprecated
     */
    public function add($element)
    {
        return $this->right($element);
    }

    /**
     * 用户自定义导航栏
     *
     * @return AbstractNavbar|null
     */
    protected function getCustomNavbar()
    {
        $class = AdminNavbarCollector::getCollector();
        if (!$class) {
            return null;
        }
        if (!class_exists($class)) {
            throw new \UnexpectedValueException('自定义导航栏类'.$class.'不存在');
        }

        $navbar = new $class();

        if (!$navbar instanceof AbstractNavbar) {
            throw new \UnexpectedValueException('自定义导航栏类'.$class.'必须继承'.AbstractNavbar::class);
        }

        return $navbar;
    }

    /**
     * 构建用户自定义导航栏内容
     */
    protected function buildCustomNavbar()
    {
        $navbar = $this->getCustomNavbar();
        if (!$navbar) {
            return;
        }

        $navbar->build($this);
    }

    /**
     * @return string
     */
    protected function getView()
    {
        $navbar = $this->getCustomNavbar();
        if (!$navbar) {
            return $this->view;
        }

        return $navbar->getView() ?: $this->view;
    }

    /**
     * @return string
     */
    public function render()
    {
        $this->buildCustomNavbar();

        $left = $this->build('left');
        $right = $this->build();

        return blade($this->getView(), ['left' => &$left, 'right' => $right])->render();
    }

    /**
     * @param string $part
     *
     * @return mixed
     */
    protected function build($part = 'right')
    {
        if (!isset($this->elements[$part]) || $this->elements[$part]->isEmpty()) {
            return '';
        }

        return $this->elements[$part]->map(function ($element) {
            if ($element instanceof Htmlable) {
                return $element->toHtml();
            }

            if ($element instanceof Renderable) {
                return $element->render();
            }

            return (string) $element;
        })->implode('');
    }
}
