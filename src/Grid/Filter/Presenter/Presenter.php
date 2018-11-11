<?php

namespace Swoft\Admin\Grid\Filter\Presenter;

use Swoft\Admin\Grid\Filter\AbstractFilter;

abstract class Presenter
{
    /**
     * @var array
     */
    public static $js = [];

    /**
     * @var array
     */
    public static $css = [];

    /**
     * @var string
     */
    protected $view = '';

    /**
     * @var int
     */
    protected $width = null;

    /**
     * @var AbstractFilter
     */
    protected $filter;

    /**
     * Set parent filter.
     *
     * @param AbstractFilter $filter
     */
    public function setParent(AbstractFilter $filter)
    {
        $this->filter = $filter;
        if ($this->width) {
            $this->width($this->width);
        }
    }

    /**
     * 宽度设置（1-12）
     *
     * @param int $width
     * @return $this
     */
    public function width(int $width)
    {
        $this->filter->width($width);
        return $this;
    }

    /**
     * @see https://stackoverflow.com/questions/19901850/how-do-i-get-an-objects-unqualified-short-class-name
     *
     * @return string
     */
    public function view(): string
    {
        if ($this->view) {
            return $this->view;
        }
        $reflect = new \ReflectionClass(get_called_class());

        return 'admin::filter.' . strtolower($reflect->getShortName());
    }

    /**
     * 设置表单重置事件
     *
     * @param string $script
     * @return $this
     */
    public function onReset(string $script)
    {
        $this->filter->onReset($script);
        return $this;
    }

    /**
     * 获取当前元素选择器
     *
     * @return string
     */
    public function getElementSelector()
    {
        return "#{$this->filter->getFilterID()} .{$this->filter->getId()}";
    }

    /**
     * Set default value for filter.
     *
     * @param $default
     *
     * @return $this
     */
    public function default($default)
    {
        $this->filter->default($default);

        return $this;
    }

    /**
     * Blade template variables for this presenter.
     *
     * @return array
     */
    public function variables(): array
    {
        return [];
    }
}
