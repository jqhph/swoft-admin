<?php

namespace Swoft\Admin;

use Closure;
use Swoft\Admin\Exception\Handler;
use Swoft\Admin\Form\Builder;
use Swoft\Admin\Form\Field;
use Swoft\Admin\Form\Layout;
use Swoft\Admin\Form\Tab;
use Swoft\Admin\Traits\FormModel;
use Swoft\Core\Coroutine;
use Swoft\Exception\BadMethodCallException;
use Swoft\Support\Contracts\Renderable;
use Swoft\Db\Model;
use Swoft\Support\HttpInput;
use Swoft\Support\Input;
use Swoft\Support\Validator;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Form.
 *
 * @method Field\Text           text($column, $label = '')
 * @method Field\Checkbox       checkbox($column, $label = '')
 * @method Field\Radio          radio($column, $label = '')
 * @method Field\Select         select($column, $label = '')
 * @method Field\MultipleSelect multipleSelect($column, $label = '')
 * @method Field\Textarea       textarea($column, $label = '')
 * @method Field\Hidden         hidden($column, $label = '')
 * @method Field\Id             id($column, $label = '')
 * @method Field\Ip             ip($column, $label = '')
 * @method Field\Url            url($column, $label = '')
 * @method Field\Color          color($column, $label = '')
 * @method Field\Email          email($column, $label = '')
 * @method Field\Mobile         mobile($column, $label = '')
 * @method Field\Slider         slider($column, $label = '')
 * @method Field\Map            map($latitude, $longitude, $label = '')
 * @method Field\Editor         editor($column, $label = '')
 * @method Field\File           file($column, $label = '')
 * @method Field\Image          image($column, $label = '')
 * @method Field\Date           date($column, $label = '')
 * @method Field\Datetime       datetime($column, $label = '')
 * @method Field\Time           time($column, $label = '')
 * @method Field\Year           year($column, $label = '')
 * @method Field\Month          month($column, $label = '')
 * @method Field\DateRange      dateRange($start, $end, $label = '')
 * @method Field\DateTimeRange  datetimeRange($start, $end, $label = '')
 * @method Field\TimeRange      timeRange($start, $end, $label = '')
 * @method Field\Number         number($column, $label = '')
 * @method Field\Currency       currency($column, $label = '')
 * @method Field\HasMany        hasMany($relationName, $callback)
 * @method Field\SwitchField    switch($column, $label = '')
 * @method Field\Display        display($column, $label = '')
 * @method Field\Rate           rate($column, $label = '')
 * @method Field\Divide         divider()
 * @method Field\Password       password($column, $label = '')
 * @method Field\Decimal        decimal($column, $label = '')
 * @method Field\Html           html($html, $label = '')
 * @method Field\Tags           tags($column, $label = '')
 * @method Field\Icon           icon($column, $label = '')
 * @method Field\Embeds         embeds($column, $label = '')
 * @method Field\MultipleImage  multipleImage($column, $label = '')
 * @method Field\MultipleFile   multipleFile($column, $label = '')
 * @method Field\Captcha        captcha($column, $label = '')
 * @method Field\Listbox        listbox($column, $label = '')
 * @method Field\Tree           tree($column, $label = '')
 */
class Form implements Renderable
{
    use FormModel;

    const STYLE_ROW = 'row';

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * Input data.
     *
     * @var array
     */
    protected $inputs = [];

    /**
     * Available fields.
     *
     * @var array
     */
    public static $availableFields = [];

    /**
     * @var array
     */
    protected static $collectedFields = [];

    /**
     * @var Form\Tab
     */
    protected $tab = null;

    /**
     * Remove flag in `has many` form.
     */
    const REMOVE_FLAG_NAME = '_remove_';

    /**
     * @var string
     */
    protected $style = '';

    /**
     * @var HttpInput
     */
    protected $input;

    /**
     * 当前记录id
     *
     * @var mixed
     */
    protected $id;

    /**
     * Create a new form instance.
     *
     * @param \Closure $callback
     */
    public function __construct(Closure $callback = null)
    {
        $this->builder = new Builder($this);

        $this->input = Input::make();

        if ($callback instanceof Closure) {
            $callback($this);
        }
    }

    /**
     * 判断是否是编辑模式
     * 需要在调用update或edit方法之后调用才有效
     *
     * @return bool
     */
    public function isEditMode()
    {
        return $this->builder->isMode(Builder::MODE_EDIT);
    }

    /**
     * 判断是否是创建模式
     *
     * @return bool
     */
    public function isCreateMode()
    {
        return $this->builder->isMode(Builder::MODE_CREATE);
    }

    /**
     * 获取需要注入原始数据的字段
     *
     * @return array
     */
    public function getNeedOriginalValueFields(): array
    {
        $columns = [];

        /* @var Field $field */
        foreach ($this->builder->fields() as $field) {
            if ($field->needOriginal()) {
                $columns[str__slug($field->column(), '_')] = $field;
            }
        }

        return $columns;
    }

    /**
     * Set original data for each field.
     *
     * @param array $values
     */
    public function setFieldOriginalValue(array $values)
    {
        admin_debug('设置字段原始数据', $values);
        $this->builder->fields()->each(function (Field $field) use ($values) {
            $field->setOriginal($values);
        });
    }

    /**
     * @param Field $field
     *
     * @return $this
     */
    public function pushField(Field $field)
    {
        $field->setForm($this);

        $this->builder->pushField($field);

        static::collectField($field);

        return $this;
    }

    /**
     * 收集字段获取静态资源
     *
     * @param Field $field
     */
    public static function collectField(Field $field)
    {
        $tid = Coroutine::tid();
        if (empty($tid)) {
            static::$collectedFields[$tid] = [];
        }
        static::$collectedFields[$tid][] = $field;
    }

    /**
     * @return Builder
     */
    public function builder()
    {
        return $this->builder;
    }

    /**
     * Generate a edit form.
     *
     * @param string|int $id
     * @return $this|ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function edit($id = null)
    {
        $id = $id ?: $this->id;
        if (!$id) {
            return $this->redirectNotFound();
        }
        $this->id = $id;

        $this->builder->setMode(Builder::MODE_EDIT);
        $this->builder->setResourceId($id);

        $data = Admin::repository()->findForEdit($this);

        if (empty($data)) {
            return $this->redirectNotFound();
        }

        $this->setFieldValue($data);

        return $this;
    }

    /**
     * 没有数据跳转回上一级
     *
     * @return ResponseInterface
     */
    protected function redirectNotFound()
    {
        admin_notice(t('Server busy or no record found.', 'admin'), 'warning');

        return redirect_back();
    }

    /**
     * 获取编辑页面id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 设置主键值
     *
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Use tab to split form.
     *
     * @param string  $title
     * @param Closure $content
     *
     * @return $this
     */
    public function tab($title, Closure $content, $active = false)
    {
        $this->getTab()->append($title, $content, $active);

        return $this;
    }

    /**
     * Get Tab instance.
     *
     * @return Tab
     */
    public function getTab()
    {
        if (is_null($this->tab)) {
            $this->tab = new Tab($this);
        }

        return $this->tab;
    }

    /**
     * Ignore fields to save.
     *
     * @param string|array $fields
     *
     * @return $this
     */
    public function ignore($fields)
    {
        $this->ignored = array_merge($this->ignored, (array) $fields);

        return $this;
    }

    /**
     * @param array        $data
     * @param string|array $columns
     *
     * @return array|mixed
     */
    protected function getDataByColumn($data, $columns)
    {
        if (is_string($columns)) {
            return array_get($data, $columns);
        }

        if (is_array($columns)) {
            $value = [];
            foreach ($columns as $name => $column) {
                if (!array_has($data, $column)) {
                    continue;
                }
                $value[$name] = array_get($data, $column);
            }

            return $value;
        }
    }

    /**
     * Find field object by column.
     *
     * @param $column
     *
     * @return mixed
     */
    protected function getFieldByColumn($column)
    {
        return $this->builder->fields()->first(
            function (Field $field) use ($column) {
                if (is_array($field->column())) {
                    return in_array($column, $field->column());
                }

                return $field->column() == $column;
            }
        );
    }

    /**
     * Set all fields value in form.
     *
     * @param array $data
     * @return void
     */
    protected function setFieldValue(array $data)
    {
        $this->builder->fields()->each(function (Field $field) use ($data) {
            if (!in_array($field->column(), $this->ignored)) {
                $field->fill($data);
            }
        });
    }

    /**
     * Set field validator.
     *
     * @param callable $validator
     *
     * @return $this
     */
    public function validator(callable $validator)
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Set action for form.
     *
     * @param string $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->builder()->setAction($action);

        return $this;
    }

    /**
     * Set field and label width in current form.
     *
     * @param int $fieldWidth
     * @param int $labelWidth
     *
     * @return $this
     */
    public function setWidth($fieldWidth = 8, $labelWidth = 2)
    {
        $this->builder()->fields()->each(function ($field) use ($fieldWidth, $labelWidth) {
            /* @var Field $field  */
            $field->width($fieldWidth, $labelWidth);
        });

        $this->builder()->setWidth($fieldWidth, $labelWidth);

        return $this;
    }

    /**
     * Set view for form.
     *
     * @param string $view
     *
     * @return $this
     */
    public function setView($view)
    {
        $this->builder()->setView($view);

        return $this;
    }

    /**
     * Set title for form.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title = '')
    {
        $this->builder()->setTitle($title);

        return $this;
    }

    /**
     * 布局
     *
     * @param Closure $closure
     * @return Layout
     */
    public function layout(\Closure $closure = null)
    {
        return $this->builder->layout($closure);
    }

    /**
     * 增加一列内容
     *
     * @param int $width
     * @param Closure $callback
     */
    public function column(int $width, \Closure $callback)
    {
        $layout = $this->builder->layout();

        $layout->column($width, $callback($layout->form()));
    }

    /**
     * 设置列宽度
     *
     * @param int $width
     * @return $this
     */
    public function setDefaultColumnWidth(int $width)
    {
        $this->builder->setDefaultColumnWidth($width);

        return $this;
    }

    /**
     * 表单元素布局风格设置
     * 暂只支持 row 模式
     *
     * @param string $style
     * @return string|void
     */
    public function style(string $style = null)
    {
        if ($style === null) {
            return $this->style;
        }

        if (!in_array($style, [static::STYLE_ROW])) {
            throw new \InvalidArgumentException("Style($style) is not supported");
        }

        $this->style = $style;
        /* @var Field $field */
        foreach ($this->builder->fields() as $field) {
            $field->style($style);
        }
    }

    /**
     * Tools setting for form.
     *
     * @param Closure $callback
     */
    public function tools(Closure $callback)
    {
        call_user_func($callback, $this->builder->getTools());
    }

    /**
     * 禁用返回列表按钮
     *
     * @return $this
     */
    public function disableListButton()
    {
        $this->builder->getTools()->disableList();
        return $this;
    }

    /**
     * 禁用视图按钮
     *
     * @return $this
     */
    public function disableViewButton()
    {
        $this->builder->getTools()->disableView();
        return $this;
    }

    /**
     * 禁用删除按钮
     *
     * @return $this
     */
    public function disableDeleteButton()
    {
        $this->builder->getTools()->disableDelete();
        return $this;
    }

    /**
     * Disable form submit.
     *
     * @return $this
     *
     * @deprecated
     */
    public function disableSubmit()
    {
        $this->builder()->getFooter()->disableSubmit();

        return $this;
    }

    /**
     * Disable form reset.
     *
     * @return $this
     *
     * @deprecated
     */
    public function disableReset()
    {
        $this->builder()->getFooter()->disableReset();

        return $this;
    }

    /**
     * Disable View Checkbox on footer.
     *
     * @return $this
     */
    public function disableViewCheck()
    {
        $this->builder()->getFooter()->disableViewCheck();

        return $this;
    }

    /**
     * Disable Editing Checkbox on footer.
     *
     * @return $this
     */
    public function disableEditingCheck()
    {
        $this->builder()->getFooter()->disableEditingCheck();

        return $this;
    }

    /**
     * Footer setting for form.
     *
     * @param Closure $callback
     */
    public function footer(Closure $callback)
    {
        call_user_func($callback, $this->builder()->getFooter());
    }

    /**
     * Render the form contents.
     *
     * @return string
     */
    public function render()
    {
        try {
            static::collectFieldAssets();

            if ($this->isCreateMode()) {
                $this->builder->getTools()->disableView();
            }

            return $this->builder->render();
        } catch (\Exception $e) {
            if (Admin::isDebug()) {
                return Handler::renderException($e);
            } else {
                throw $e;
            }
        }
    }

    /**
     * Get or set input data.
     *
     * @param string $key
     * @param null   $value
     *
     * @return array|mixed
     */
    public function input($key, $value = null)
    {
        if (is_null($value)) {
            return array_get($this->inputs, $key);
        }

        return array_set($this->inputs, $key, $value);
    }

    /**
     * 移除用户输入的值
     *
     * @param string|array $key
     * @return $this
     */
    public function deleteInput($key)
    {
        if (is_array($key)) {
            foreach ($key as &$k) {
                $this->deleteInput($key);
            }
            return $this;
        }
        unset($this->inputs[$key]);
        return $this;
    }

    /**
     * Register custom field.
     *
     * @param string $abstract
     * @param string $class
     *
     * @return void
     */
    public static function extend($abstract, $class)
    {
        static::$availableFields[$abstract] = $class;
    }

    /**
     * Remove registered field.
     *
     * @param array|string $abstract
     */
    public static function forget($abstract)
    {
        array_forget(static::$availableFields, $abstract);
    }

    /**
     * Find field class.
     *
     * @param string $method
     *
     * @return bool|mixed
     */
    public static function findFieldClass($method)
    {
        $class = array_get(static::$availableFields, $method);

        if (class_exists($class)) {
            return $class;
        }

        return false;
    }

    /**
     * Collect assets required by registered field.
     *
     * @return array
     */
    public static function collectFieldAssets()
    {
        $tid = Coroutine::tid();

        if (empty(static::$collectedFields[$tid])) {
            return;
        }

        $css = collect();
        $js  = collect();

        foreach (static::$collectedFields[$tid] as $field) {
            $assets = $field->getAssets();

            $css = $css->merge($assets['css']);
            $js  = $js->merge($assets['js']);
        }
        unset(static::$collectedFields[$tid]);

        Admin::css($css->flatten()->unique()->filter()->toArray());
        Admin::js($js->flatten()->unique()->filter()->toArray());
    }

    /**
     * 释放资源
     */
    public static function release()
    {
        $tid = Coroutine::tid();
        unset(static::$collectedFields[$tid]);
    }

    /**
     * Getter.
     *
     * @param string $name
     *
     * @return array|mixed
     */
    public function __get($name)
    {
        return $this->input($name);
    }

    /**
     * Setter.
     *
     * @param string $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->input($name, $value);
    }

    /**
     * Generate a Field object and add to form builder if Field exists.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return Field
     */
    public function __call($method, $arguments)
    {
        if ($className = static::findFieldClass($method)) {
            $column = array_get($arguments, 0, ''); //[0];

            $element = new $className($column, array_slice($arguments, 1));

            $this->pushField($element);

            return $element;
        }

        throw new BadMethodCallException("Field type [$method] does not exist.");
    }
}
