<?php

namespace Swoft\Admin\Grid;

use Swoft\Admin\Grid;
use Swoft\Admin\Traits\ModelTree;
use Swoft\Support\Collection;
use Swoft\Support\Contracts\Renderable;
use Swoft\Admin\Grid\Displayers\Tree as TreeDisplayer;

class Tree implements Renderable
{
    /**
     * @var string
     */
    protected static $parentColumn = 'parent_id';

    /**
     * @var string
     */
    protected static $orderColumn = 'priority';

    /**
     *
     * @var string
     */
    protected static $childrenColumn = 'children';

    /**
     * @var Grid
     */
    protected $grid;

    /**
     * @var Row[]
     */
    protected $rows;

    /**
     * @var int
     */
    protected $hierarchy = 1;

    /**
     * @var int
     */
    protected $spacing = 0;

    public function __construct(int $hierarchy, Collection $rows, Grid $grid)
    {
        $this->grid = $grid;
        $this->rows = $rows;
        $this->hierarchy = $hierarchy;

        $this->spacing = $this->hierarchy * 5;
    }

    /**
     * @return string
     */
    public static function getParentColumn()
    {
        return self::$parentColumn;
    }

    /**
     * Set parent column.
     *
     * @param string $column
     */
    public static function setParentColumn(string $column)
    {
        self::$parentColumn = $column;
    }

    /**
     * Get order column name.
     *
     * @return string
     */
    public static function getOrderColumn()
    {
        return self::$orderColumn;
    }

    /**
     * Set order column.
     *
     * @param string $column
     */
    public static function setOrderColumn(string $column)
    {
        self::$orderColumn = $column;
    }

    /**
     * Get children column name.
     *
     * @return string
     */
    public static function getChildrenColumn()
    {
        return self::$childrenColumn;
    }

    /**
     * Set children column.
     *
     * @param string $column
     */
    public static function setChildrenColumn(string $column)
    {
        self::$childrenColumn = $column;
    }


    /**
     * 生成层级结构
     *
     * @param string $keyName
     * @param array $data
     * @return array
     */
    public static function make($keyName, array &$data)
    {
        if (!$data) {
            return $data;
        }
        if (isset(current($data)[static::$childrenColumn])) {
            return $data;
        }

        return ModelTree::buildNestedArray(
            $data,
            0,
            $keyName,
            static::$parentColumn,
            static::$childrenColumn,
            static::$orderColumn
        );
    }

    /**
     * 缩进处理
     *
     * @param mixed $value
     * @paran bool $end
     * @return string
     */
    protected function formatIndent($value, $end = false)
    {
        $indent = str_repeat('&nbsp;', $this->spacing);

        if ($end) {
            return "{$indent}└─ {$value}";
        }
        return "{$indent}├─ {$value}";
    }

    /**
     * @return string
     */
    public function render()
    {
        $tr  = '';
        $end = count($this->rows) - 1;

        foreach ($this->rows as $k => $row) {
            $tr .= '<tr>';

            foreach ($this->grid->columnNames as &$name) {
                $value = $row->column($name);
                if ($value instanceof TreeDisplayer) {
                    $value->setHierarchy($this->hierarchy + 1);
                    $value = $this->formatIndent($value->display(), $end == $k);
                }

                $tr .= "<td {$row->getColumnAttributes($name)}>{$value}</td>";
            }
            $tr .= '</tr>';

            if ($tree = $this->grid->pullTree()) {
                $tr .= $tree->render();
            }
        }

        return $tr;
    }
}
