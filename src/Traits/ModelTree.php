<?php

namespace Swoft\Admin\Traits;


trait ModelTree
{
    /**
     * @var array
     */
    protected static $branchOrder = [];

    /**
     * @var string
     */
    protected static $primaryColumn = 'id';

    /**
     * @var string
     */
    protected static $parentColumn = 'parent_id';

    /**
     * @var string
     */
    protected static $titleColumn = 'title';

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
     * @var string
     */
    protected static $pathColumn = 'path';

    /**
     * @var string
     */
    protected static $iconColumn = 'icon';

    /**
     * @return string
     */
    public static function getPrimaryColumn()
    {
        return self::$primaryColumn;
    }

    /**
     *
     * @param string $column
     */
    public static function setPrimaryColumn(string $column)
    {
        self::$primaryColumn = $column;
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
     * Get title column.
     *
     * @return string
     */
    public static function getTitleColumn()
    {
        return self::$titleColumn;
    }

    /**
     * Set title column.
     *
     * @param string $column
     */
    public static function setTitleColumn(string $column)
    {
        self::$titleColumn = $column;
    }

    /**
     * @return string
     */
    public static function getPathColumn()
    {
        return self::$pathColumn;
    }

    /**
     * @param string $column
     */
    public static function setPathColumn(string $column)
    {
        self::$pathColumn = $column;
    }

    /**
     * @return string
     */
    public static function getIconColumn()
    {
        return self::$iconColumn;
    }

    /**
     * @param string $column
     */
    public static function setIconColumn(string $column)
    {
        self::$iconColumn = $column;
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
     * Format data to tree like array.
     *
     * @return array
     */
    public function toTree()
    {
        return static::buildNestedArray($this->allNodes());
    }

    /**
     * 按$key字段值给层级树状数组正序排序
     *
     * @param  array  $tree 层级树状数组
     * @param  string $key 排序依据字段键名
     * @return array
     */
    public static function sort(array $tree, $key = null)
    {
        $key = $key ?: static::$orderColumn;

        // 顶级菜单排序
        static::quickSort($tree, $key, 0, count($tree) - 1);

        // 子菜单排序
        foreach ($tree as &$r) {
            if (empty($r[static::$childrenColumn])) {
                continue;
            }

            // 递归排序子菜单的子菜单
            static::sort($r[static::$childrenColumn], $key);
        }

        return $tree;
    }

    protected static function quickSort(array &$sort, $k, $start, $end)
    {
        if ($start >= $end) {
            return;
        }
        $i = $start;
        $j = $end + 1;
        while (1) {
            do {
                $i++;
            } while (! ($sort[$start][$k] <= $sort[$i][$k] || $i == $end));

            do {
                $j--;
            } while (! ($sort[$j][$k]  <= $sort[$start][$k] || $j == $start));

            if ($i < $j) {
                $temp 	  = $sort[$i];
                $sort[$i] = $sort[$j];
                $sort[$j] = $temp;
            } else {
                break;
            }
        }
        $temp		  = $sort[$start];
        $sort[$start] = $sort[$j];
        $sort[$j]     = $temp;

        self::quickSort($sort, $k, $start, $j - 1);
        self::quickSort($sort, $k, $j + 1, $end);
    }

    /**
     * 递归生成树状结构数据
     *
     * @param array $nodes
     * @param int $parentId
     * @param string $primaryKeyName 主键名称，如果小写下划线命名字段不存在。会转化为驼峰判断
     * @param string $parentKeyName  父级id名称
     * @param string $childrenKeyName 生成子级数据键名，如果小写下划线命名字段不存在。会转化为驼峰判断
     * @param string $orderKeyName 排序键名，不会转化为驼峰判断
     * @return array
     */
    public static function buildNestedArray(
        array $nodes = [],
        $parentId = 0,
        $primaryKeyName = null,
        $parentKeyName = null,
        $childrenKeyName = null,
        $orderKeyName = null
    )
    {
        $hasOrderKey = false;
        $branch = [];
        $primaryKeyName = $primaryKeyName ?: self::$primaryColumn;
        $parentKeyName = $parentKeyName ?: self::$parentColumn;
        $childrenKeyName = $childrenKeyName ?: self::$childrenColumn;
        $orderKeyName = $orderKeyName ?: self::$orderColumn;

        $parentKeyNameCC = camel__case($parentKeyName, '_');
        $priamryKeyNameCC = camel__case($primaryKeyName, '_');

        foreach ($nodes as $node) {
            if (isset($node[$orderKeyName])) {
                $hasOrderKey = true;
            }

            $pk = isset($node[$parentKeyName]) ?
                $node[$parentKeyName] :
                array_get($node, $parentKeyNameCC);
            if ($pk == $parentId) {
                $children = static::buildNestedArray(
                    $nodes,
                    isset($node[$primaryKeyName]) ? $node[$primaryKeyName] : array_get($node, $priamryKeyNameCC),
                    $primaryKeyName,
                    $parentKeyName,
                    $childrenKeyName,
                    $orderKeyName
                );

                if ($children) {
                    $node[$childrenKeyName] = $children;
                }

                $branch[] = $node;
            }
        }

        return $hasOrderKey ? static::sort($branch, $orderKeyName) : $branch;
    }

    /**
     * Get options for Select field in form.
     *
     * @return \Swoft\Support\Collection
     */
    public static function selectOptions()
    {
        $options = (new static())->buildSelectOptions();

        return collect($options)->prepend('Root', 0)->all();
    }

    /**
     * Build options of select field in form.
     *
     * @param array  $nodes
     * @param int    $parentId
     * @param string $prefix
     *
     * @return array
     */
    protected function buildSelectOptions(array $nodes = [], $parentId = 0, $prefix = '')
    {
        $prefix = $prefix ?: str_repeat('&nbsp;', 6);

        $options = [];

        if (empty($nodes)) {
            $nodes = $this->allNodes();
        }

        foreach ($nodes as $node) {
            $node[self::$titleColumn] = $prefix.'&nbsp;'.$node[self::$titleColumn];
            if ($node[self::$parentColumn] == $parentId) {
                $children = $this->buildSelectOptions($nodes, $node[self::$primaryColumn], $prefix.$prefix);

                $options[$node[self::$primaryColumn]] = $node[self::$titleColumn];

                if ($children) {
                    $options += $children;
                }
            }
        }

        return $options;
    }
    
}
