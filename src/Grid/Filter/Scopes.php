<?php

namespace Swoft\Admin\Grid\Filter;

use Swoft\Admin\Admin;
use Swoft\Admin\Grid;
use Swoft\Support\Collection;

class Scopes extends Collection
{
    /**
     * @var Grid\Filter
     */
    protected $filter;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $style = 'dropbox';

    /**
     * 默认选中的key
     *
     * @var mixed
     */
    protected $default;

    /**
     * @var string
     */
    protected $cancelLabel;

    public function __construct(Grid\Filter $filter, string $type)
    {
        $this->filter = $filter;
        $this->type = $type;
    }

    /**
     * 默认选中
     */
    public function select($key)
    {
        $this->default = $key;

        return $this;
    }

    /**
     * 获取默认选中的key
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * 设置取消按钮名称
     *
     * @param string $label
     * @return $this
     */
    public function setCancelLabel(string $label)
    {
        $this->cancelLabel = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getCancelLabel()
    {
        return $this->cancelLabel ?: t('Cancel', 'admin');
    }

    /**
     * @param string $key
     * @param string $label
     * @return Scope
     */
    public function add($key, string $label = '')
    {
        $scope = new Scope($this, $key, $label);

        $this->push($scope);

        return $scope;
    }

    /**
     * @return Grid\Filter
     */
    public function parent()
    {
        return $this->filter;
    }

    /**
     * 类型
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label ?: Admin::translateField($this->type);
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getStyle()
    {
        return 'btn-'.$this->style;
    }


    /**
     * 设置按钮颜色风格
     *
     * @param string $style
     * @return $this
     */
    public function style(string $style)
    {
        $this->style = $style;
        return $this;
    }

    /**
     * @return $this
     */
    public function danger()
    {
        return $this->style('danger');
    }
}
