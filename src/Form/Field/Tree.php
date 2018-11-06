<?php

namespace Swoft\Admin\Form\Field;

use Swoft\Admin\Traits\ModelTree;
use Swoft\Contract\Arrayable;

class Tree extends Select
{
    protected $view = 'admin::form.tree';

    /**
     * 树层级
     *
     * @var int
     */
    protected $tier = 1;

    /**
     * @var string
     */
    protected $labelColumn = 'title';

    /**
     * @var string
     */
    protected $primaryKeyName = 'id';

    /**
     * @var string
     */
    protected $parentColumn = 'parent_id';

    /**
     * @var string
     */
    protected $orderColumn = 'priority';

    /**
     *
     * @var string
     */
    protected $childrenColumn = 'children';

    /**
     * 设置主键名称
     *
     * @param string $key
     * @return $this
     */
    public function setKeyName(string $key)
    {
        $this->primaryKeyName = $key;
        return $this;
    }

    /**
     * 设置展示字段名称
     *
     * @param string $key
     * @return $this
     */
    public function setLabelKeyName(string $key)
    {
        $this->labelColumn = $key;
        return $this;
    }

    /**
     * 设置父级id字段名称
     *
     * @param string $key
     * @return $this
     */
    public function setParentKeyName(string $key)
    {
        $this->parentColumn = $key;
        return $this;
    }

    /**
     * 设置排序字段名称
     *
     * @param string $key
     * @return $this
     */
    public function setOrderKeyName(string $key)
    {
        $this->orderColumn = $key;
        return $this;
    }

    /**
     * Set options.
     *
     * @param array|callable|string $options
     *
     * @return $this|mixed
     */
    public function options($options = [])
    {
        // remote options
        if (is_string($options)) {
            return $this->loadRemoteOptions(...func_get_args());
        }

        if ($options instanceof Arrayable) {
            $options = $options->toArray();
        }

        if (is_callable($options)) {
            $this->options = $options;
        } else {
            $this->options = (array) $options;
        }

        if ($this->options) {
            $this->buildTree();
        }

        return $this;
    }

    public function prepare($value)
    {
        return $value == -99 ? 0 : $value;
    }

    /**
     * 生成树状结构
     */
    protected function buildTree()
    {
        $this->options = ModelTree::buildNestedArray(
            $this->options,
            0,
            $this->primaryKeyName,
            $this->parentColumn,
            $this->childrenColumn,
            $this->orderColumn
        );

        $new = [];
        foreach ($this->options as $k => &$row) {
            $new[$row[$this->labelColumn]] = $row[$this->primaryKeyName];

            if (empty($row[$this->childrenColumn])) continue;

            $new = array_merge($new, $this->buildRows($row[$this->childrenColumn], $this->tier));

            unset($row[$this->childrenColumn]);
        }

        $this->options = array_flip($new);
    }

    protected function buildRows(array &$options, $tier = 1)
    {
        $new = [];
        $end = count($options) - 1;
        foreach ($options as $k => &$row) {
            $row[$this->labelColumn] = $this->formatIndent($tier, $row[$this->labelColumn], $k == $end);

            $new[$row[$this->labelColumn]] = $row[$this->primaryKeyName];
            if (! empty($row[$this->childrenColumn])) {
                $this->tier++;
                $new = array_merge($new, $this->buildRows($row[$this->childrenColumn], $this->tier));
            }
            unset($row[$this->childrenColumn]);
        }

        return $new;
    }

    /**
     * @param int $tier
     * @param mixed $value
     * @param bool $end
     * @return string
     */
    protected function formatIndent(int $tier, $value, bool $end = false)
    {
        $indent = str_repeat('&nbsp;', $tier * 3);

        if ($end) {
            return "{$indent}└─ {$value}";
        }
        return "{$indent}├─ {$value}";
    }
}
