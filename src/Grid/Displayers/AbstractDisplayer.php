<?php

namespace Swoft\Admin\Grid\Displayers;

use Swoft\Admin\Grid;
use Swoft\Admin\Grid\Column;
use Swoft\Admin\Widgets\Widget;
use Swoft\Db\Collection;
use Swoft\Support\Fluent;

abstract class AbstractDisplayer extends Widget
{
    /**
     * @var Grid
     */
    protected $grid;

    /**
     * @var Column
     */
    protected $column;

    /**
     * @var Fluent
     */
    public $row;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * Create a new displayer instance.
     *
     * @param mixed      $value
     * @param Grid       $grid
     * @param Column     $column
     * @param Fluent     $originalRow
     */
    public function __construct($value, Grid $grid, Column $column, Fluent $originalRow)
    {
        $this->value = $value;
        $this->grid = $grid;
        $this->column = $column;
        $this->row = $originalRow;

    }

    /**
     * Get key of current row.
     *
     * @return mixed
     */
    public function getKeyName()
    {
        return $this->grid->getKeyName();
    }

    /**
     * 获取主键值
     *
     * @return mixed
     */
    public function getPrimaryKey()
    {
        return $this->row->get($this->getKeyName());
    }

    /**
     * 获取字段值
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * 设置参值
     *
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * 获取行数据
     *
     * @return Fluent
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * @return Column
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Display method.
     *
     * @return mixed
     */
    abstract public function display();

    public function render()
    {
        return $this->display();
    }
}
