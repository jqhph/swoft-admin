<?php

namespace Swoft\Admin\Grid;

use Swoft\Admin\Grid\Displayers\Traits\Helper;
use Closure;
use Swoft\Admin\Admin;
use Swoft\Support\Fluent;
use Swoft\Admin\Grid;
use Swoft\Admin\Grid\Displayers\AbstractDisplayer;
use Swoft\Contract\Arrayable;
use Swoft\Support\Str;
use Swoft\Support\Url;

/**
 * @method $this switch($statesOrCallback = [], string $color = '')
 * @method $this editable($method = null, array $options = []);
 * @method $this image($serverOrCallback = '', int $width = 200, int $height = 200);
 * @method $this label($styleOrCallback = 'success');
 * @method $this button($styleOrCallback = 'success');
 * @method $this link($hrefOrCallback = '', $target = '_blank');
 * @method $this badge($styleOrCallback = 'red');
 * @method $this progressBar($styleOrCallback = 'primary', $size = 'sm', $max = 100)
 * @method $this checkbox($optionsOrCallback = [])
 * @method $this radio($optionsOrCallback = [])
 * @method $this expand($labelOrCallback = '', bool $dump = null)
 * @method $this table($titlesOrCallback = [])
 * @method $this tree()
 *
 * // 字符串帮助方法
 * @method $this limit($limit = 100, $end = '...')
 * @method $this ascii()
 * @method $this camel()
 * @method $this finish($cap)
 * @method $this lower()
 * @method $this words($words = 100, $end = '...')
 * @method $this upper()
 * @method $this title()
 * @method $this slug($separator = '-')
 * @method $this snake($delimiter = '_')
 * @method $this studly()
 * @method $this substr($start, $length = null)
 * @method $this ucfirst()
 */
class Column
{
    use Helper;

    /**
     * @var Grid
     */
    protected $grid;

    /**
     * Name of column.
     *
     * @var string
     */
    protected $name;

    /**
     * Label of column.
     *
     * @var string
     */
    protected $label;

    /**
     * Original value of column.
     *
     * @var mixed
     */
    protected $original;

    /**
     * Is column sortable.
     *
     * @var bool
     */
    protected $sortable = false;

    /**
     * Sort arguments.
     *
     * @var array
     */
    protected $sort;

    /**
     * Attributes of column.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Original grid data.
     *
     * @var array
     */
    protected static $originalGridData = [];

    /**
     * @var []Closure
     */
    protected $displayCallbacks = [];

    /**
     * Displayers for grid column.
     *
     * @var array
     */
    public static $displayers = [];

    /**
     * Defined columns.
     *
     * @var array
     */
    public static $defined = [];

    /**
     * @var array
     */
    protected $titleHtmlAttributes = [];

    /**
     * @var string
     */
    protected $value = '';

    /**
     * 设置宽度(px或百分比)
     *
     * @var string
     */
    protected $width;

    const SELECT_COLUMN_NAME = '__row_selector__';

    /**
     * @param string $name
     * @param string $label
     */
    public function __construct($name, $label)
    {
        $this->name = $name;

        $this->label = $this->formatLabel($label);
    }

    /**
     * 设置标题名称
     *
     * @param string|\Closure $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = value($label);

        return $this;
    }

    /**
     * Extend column displayer.
     *
     * @param $name
     * @param $displayer
     */
    public static function extend($name, $displayer)
    {
        static::$displayers[$name] = $displayer;
    }

    /**
     * Define a column globally.
     *
     * @param string $name
     * @param mixed  $definition
     */
    public static function define($name, $definition)
    {
        static::$defined[$name] = $definition;
    }

    /**
     * 设置宽度
     *
     * @param string $width
     * @return $this|string
     */
    public function width(string $width = null)
    {
        if ($width === null) {
            return $this->width;
        }

        $this->width = $width;
        return $this;
    }

    /**
     * Set grid instance for column.
     *
     * @param Grid $grid
     */
    public function setGrid(Grid $grid)
    {
        $this->grid = $grid;
    }

    /**
     * 调用fill方法注入数据之前请先调用此方法保存原始数据
     *
     * @param array $input
     */
    public static function setOriginalGridData(array $input)
    {
        static::$originalGridData = &$input;
    }

    /**
     * Set column attributes.
     *
     * @param array $attributes
     *
     * @return $this
     */
    public function setAttributes($attributes = [])
    {
        $attr = Admin::getContextAttribute('grid-html-column-attrs', []);

        $attr[$this->name] = &$attributes;

        Admin::setContextAttribute('grid-html-column-attrs', $attr);

        return $this;
    }

    /**
     * Set column title attributes.
     *
     * @param array $attributes
     * @return $this
     */
    public function setHeaderAttributes(array $attributes = [])
    {
        $this->titleHtmlAttributes = array_merge($this->titleHtmlAttributes, $attributes);
        return $this;
    }

    /**
     * Set column title default attributes.
     *
     * @param array $attributes
     * @return $this
     */
    public function setDefaultHeaderAttribute(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (isset($this->titleHtmlAttributes[$key])) {
                continue;
            }
            $this->titleHtmlAttributes[$key] = $value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function formatTitleAttributes()
    {
        $attrArr = [];
        foreach ($this->titleHtmlAttributes as $name => $val) {
            $attrArr[] = "$name=\"$val\"";
        }

        return implode(' ', $attrArr);
    }

    /**
     * 默认隐藏字段
     * 开启responsive模式有效
     *
     * @return $this
     */
    public function hide()
    {
        return $this->setHeaderAttributes(['data-priority' => 0]);
    }

    /**
     * 允许使用responsive
     * 开启responsive模式有效
     *
     * data-priority=”1″ 保持可见，但可以在下拉列表筛选隐藏。
     * data-priority=”2″ 480px 分辨率以下可见
     * data-priority=”3″ 640px 以下可见
     * data-priority=”4″ 800px 以下可见
     * data-priority=”5″ 960px 以下可见
     * data-priority=”6″ 1120px 以下可见
     *
     * @param int $priority
     * @return $this
     */
    public function responsive(int $priority = 1)
    {
        return $this->setHeaderAttributes(['data-priority' => $priority]);
    }

    /**
     * @return int|null
     */
    public function getDataPriority()
    {
        return isset($this->titleHtmlAttributes['data-priority']) ? $this->titleHtmlAttributes['data-priority'] : null;
    }

    /**
     * Get column attributes.
     *
     * @param string $name
     *
     * @return mixed
     */
    public static function getAttributes($name)
    {
        return array_get(Admin::getContextAttribute('grid-html-column-attrs', []), $name, '');
    }

    /**
     * Set style of this column.
     *
     * @param string $style
     *
     * @return Column
     */
    public function style($style)
    {
        return $this->setAttributes(compact('style'));
    }

    /**
     * Get name of this column.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Format label.
     *
     * @param $label
     *
     * @return mixed
     */
    protected function formatLabel($label)
    {
        if ($label) {
            return $label;
        }
        return Admin::translateField($this->name);
    }

    /**
     * Get label of the column.
     *
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Mark this column as sortable.
     *
     * @return Column
     */
    public function sortable()
    {
        $this->sortable = true;

        return $this;
    }

    /**
     * 默认倒叙排序
     *
     * @return $this
     * @throws \Exception
     */
    public function desc()
    {
        if ($column = $this->grid->getAttribute('cloumn-default-order')) {
            throw new \Exception("默认排序字段不能超过1个！已设置排序字段：{$column}。");
        }

        $this->sort['type'] = 'desc';
        $this->sort['column'] = $this->name;
        $this->grid->setAttribute('cloumn-default-order', $this->name.',desc');
        return $this;
    }

    /**
     * 默认正序排序
     *
     * @return $this
     * @throws \Exception
     */
    public function asc()
    {
        if ($column = $this->grid->getAttribute('cloumn-default-order')) {
            throw new \Exception("默认排序字段不能超过1个！已设置排序字段：{$column}。");
        }

        $this->sort['type'] = 'asc';
        $this->sort['column'] = $this->name;
        $this->grid->setAttribute('cloumn-default-order', $this->name.',asc');

        return $this;
    }

    /**
     * Add a display callback.
     *
     * @param Closure $callback
     *
     * @return $this
     */
    public function display(Closure $callback)
    {
        $this->displayCallbacks[] = $callback;

        return $this;
    }

    /**
     * Display column using array value map.
     *
     * @param array $values
     * @param null  $default
     *
     * @return $this
     */
    public function using(array $values, $default = null)
    {
        return $this->display(function ($value) use ($values, $default) {
            if (is_null($value)) {
                return $default;
            }

            return array_get($values, $value, $default);
        });
    }

    /**
     * Render this column with the given view.
     *
     * @param string $view
     *
     * @return $this
     */
    public function view($view)
    {
        return $this->display(function ($value) use ($view) {
            $model = $this;

            return blade($view, compact('model', 'value'))->render();
        });
    }

    /**
     * If has display callbacks.
     *
     * @return bool
     */
    protected function hasDisplayCallbacks()
    {
        return !empty($this->displayCallbacks);
    }

    /**
     * Call all of the "display" callbacks column.
     *
     * @param mixed  $value
     * @param Fluent $originalRow
     * @return \Closure
     */
    protected function callDisplayCallbacks($value, $originalRow)
    {
        return function () use ($originalRow, $value) {
            foreach ($this->displayCallbacks as $callback) {
                $value = call_user_func_array(
                    $this->bindOriginalRow($originalRow, $callback),
                    [$value, $this]
                );

            }
            return $value;
        };
    }

    /**
     * @return string
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * Set original grid data to column.
     *
     * @param Fluent $row
     * @param Closure $callback
     * @return Closure
     */
    protected function bindOriginalRow($row, Closure $callback)
    {
        return $callback->bindTo($row);
    }

    /**
     * Fill all data to every column.
     *
     * @param array $data
     *
     * @return mixed
     */
    public function fill(array $data)
    {
        foreach ($data as $key => &$row) {
            $this->original = $value = array_get($row, $this->name);
            $originalRow = new Fluent(static::$originalGridData[$key]);

            $this->value = $value = $this->htmlEntityEncode($value);

            array_set($row, $this->name, $value);

            if ($this->isDefinedColumn()) {
                $this->useDefinedColumn();
            }

            if ($this->hasDisplayCallbacks()) {
                array_set(
                    $row,
                    $this->name,
                    $this->callDisplayCallbacks($this->original, $originalRow)
                );
            }
        }

        return $data;
    }

    /**
     * If current column is a defined column.
     *
     * @return bool
     */
    protected function isDefinedColumn()
    {
        return array_key_exists($this->name, static::$defined);
    }

    /**
     * Use a defined column.
     *
     * @throws \Exception
     */
    protected function useDefinedColumn()
    {
        // clear all display callbacks.
        $this->displayCallbacks = [];

        $class = static::$defined[$this->name];

        if ($class instanceof Closure) {
            $this->display($class);

            return;
        }

        if (!class_exists($class) || !is_subclass_of($class, AbstractDisplayer::class)) {
            throw new \Exception("Invalid column definition [$class]");
        }

        $grid = $this->grid;
        $column = $this;

        $this->display(function ($value) use ($grid, $column, $class) {
            /** @var AbstractDisplayer $definition */
            $definition = new $class($value, $grid, $column, $this);

            return $definition->display();
        });
    }

    /**
     * Convert characters to HTML entities recursively.
     *
     * @param array|string $item
     *
     * @return mixed
     */
    protected function htmlEntityEncode($item)
    {
        if (is_array($item)) {
            array_walk_recursive($item, function (&$value) {
                $value = htmlentities($value);
            });
        } else {
            $item = htmlentities($item);
        }

        return $item;
    }

    /**
     * Create the column sorter.
     *
     * @return string|void
     */
    public function sorter()
    {
        if (!$this->sortable) {
            return;
        }

        $icon = 'fa-sort';
        $type = 'desc';

        if ($this->isSorted()) {
            $type = $this->sort['type'] == 'desc' ? 'asc' : 'desc';
            $icon .= "-amount-{$this->sort['type']}";
        }

        $url = Url::full([$this->grid->model()->getSortName() => ['column' => $this->name, 'type' => $type]]);

        return "<a class=\"fa fa-fw $icon\" href=\"$url\"></a>";
    }

    /**
     * Determine if this column is currently sorted.
     *
     * @return bool
     */
    protected function isSorted()
    {
        $this->sort = http_get($this->grid->model()->getSortName(), $this->sort);

        if (empty($this->sort)) {
            return false;
        }

        return isset($this->sort['column']) && $this->sort['column'] == $this->name;
    }

    /**
     * Find a displayer to display column.
     *
     * @param string $abstract
     * @param array  $arguments
     *
     * @return Column
     */
    protected function resolveDisplayer($abstract, $arguments)
    {
        if ($abstract === 'tree') {
            $this->grid->option('useTree', true);
            $this->grid->disablePagination();
        }

        if (array_key_exists($abstract, static::$displayers)) {
            return $this->callBuiltinDisplayer(static::$displayers[$abstract], $arguments);
        }

        return $this->callSupportDisplayer($abstract, $arguments);
    }

    /**
     * Call Swoft/Support displayer.
     *
     * @param string $abstract
     * @param array  $arguments
     *
     * @return Column
     */
    protected function callSupportDisplayer($abstract, $arguments)
    {
        return $this->display(function ($value) use ($abstract, $arguments) {
            if (is_array($value) || $value instanceof Arrayable) {
                return call_user_func_array([collect($value), $abstract], $arguments);
            }

            if (is_string($value)) {
                return call_user_func_array([Str::class, $abstract], array_merge([$value], $arguments));
            }

            return $value;
        });
    }

    /**
     * Call Builtin displayer.
     *
     * @param string $abstract
     * @param array  $arguments
     *
     * @return Column
     */
    protected function callBuiltinDisplayer($abstract, $arguments)
    {
        if ($abstract instanceof Closure) {
            return $this->display(function ($value) use ($abstract, $arguments) {
                return $abstract->call($this, ...array_merge([$value], $arguments));
            });
        }

        if (class_exists($abstract) && is_subclass_of($abstract, AbstractDisplayer::class)) {
            $grid = $this->grid;
            $column = $this;

            return $this->display(function ($value) use ($abstract, $grid, $column, $arguments) {
                $displayer = new $abstract($value, $grid, $column, $this);

                if (!empty($arguments[0]) && $arguments[0] instanceof \Closure) {
                    $arguments[0]($displayer);
                    array_shift($arguments);
                }

                if ($displayer instanceof Grid\Displayers\Tree) {
                    return $displayer;
                }

                return $displayer->display(...$arguments);
            });
        }

        return $this;
    }

    /**
     * Passes through all unknown calls to builtin displayer or supported displayer.
     *
     * Allow fluent calls on the Column object.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return $this
     */
    public function __call($method, $arguments)
    {
        return $this->resolveDisplayer($method, $arguments);
    }
}
