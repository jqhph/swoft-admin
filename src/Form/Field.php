<?php

namespace Swoft\Admin\Form;

use Swoft\Admin\Admin;
use Swoft\Admin\Form;
use Swoft\Contract\Arrayable;
use Swoft\Support\Collection;
use Swoft\Support\Contracts\Renderable;
use Swoft\Support\Arr;
use Swoft\Support\Traits\Macroable;
use Swoft\Support\Validator;

/**
 * Class Field.
 */
class Field implements Renderable
{
    use Macroable;

    const FILE_DELETE_FLAG = '_file_del_';

    /**
     * Element id.
     *
     * @var array|string
     */
    protected $id;

    /**
     * Element value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * 当前行数据
     *
     * @var Collection
     */
    protected $row;

    /**
     * 修改和删除操作时是否需要返回原始值
     *
     * @var bool
     */
    protected $needOriginal = false;

    /**
     * Field original value.
     *
     * @var mixed
     */
    protected $original;

    /**
     * Field default value.
     *
     * @var mixed
     */
    protected $default;

    /**
     * Element label.
     *
     * @var string
     */
    protected $label = '';

    /**
     * Column name.
     *
     * @var string|array
     */
    protected $column = '';

    /**
     * Form element name.
     *
     * @var string
     */
    protected $elementName = [];

    /**
     * Form element classes.
     *
     * @var array
     */
    protected $elementClass = [];

    /**
     * Variables of elements.
     *
     * @var array
     */
    protected $variables = [];

    /**
     * Options for specify elements.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Checked for specify elements.
     *
     * @var array
     */
    protected $checked = [];

    /**
     * Validation rules.
     *
     * @var string|\Closure
     */
    protected $rules = '';

    /**
     * Validation messages.
     *
     * @var array
     */
    protected $validationMessages = [];

    /**
     * Css required by this field.
     *
     * @var array
     */
    protected static $css = [];

    /**
     * Js required by this field.
     *
     * @var array
     */
    protected static $js = [];

    /**
     * Script for field.
     *
     * @var string
     */
    protected $script = '';

    /**
     * Element attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Parent form.
     *
     * @var Form
     */
    protected $form = null;

    /**
     * View for field to render.
     *
     * @var string
     */
    protected $view = '';

    /**
     * Help block.
     *
     * @var array
     */
    protected $help = [];

    /**
     * Key for errors.
     *
     * @var mixed
     */
    protected $errorKey;

    /**
     * Placeholder for this field.
     *
     * @var string|array
     */
    protected $placeholder;

    /**
     * 字段布局风格
     *
     * @var string
     */
    protected $style = '';

    /**
     * Width for label and field.
     *
     * @var array
     */
    protected $width = [
        'label' => 2,
        'field' => 8,
        'row'   => 12,
    ];

    /**
     * If the form horizontal layout.
     *
     * @var bool
     */
    protected $horizontal = true;

    /**
     * column data format.
     *
     * @var \Closure
     */
    protected $customFormat = null;

    /**
     * @var bool
     */
    protected $display = true;

    /**
     * @var array
     */
    protected $labelClass = [];

    /**
     * @var string
     */
    protected $classPrefix = '__';

    /**
     * Field constructor.
     *
     * @param       $column
     * @param array $arguments
     */
    public function __construct($column, $arguments = [])
    {
        $this->column = $column;
        $this->label = $this->formatLabel($arguments);
        $this->id = $this->formatId($column);
    }

    /**
     * Get assets required by this field.
     *
     * @return array
     */
    public function getAssets()
    {
        return [
            'css' => static::$css,
            'js'  => static::$js,
        ];
    }

    /**
     * Format the field element id.
     *
     * @param string|array $column
     *
     * @return string|array
     */
    protected function formatId($column)
    {
        return str_replace('.', '_', $column);
    }

    /**
     * Format the label value.
     *
     * @param array $arguments
     *
     * @return string
     */
    protected function formatLabel($arguments = [])
    {
        if (isset($arguments[0])) {
            return $arguments[0];
        }

        $column = is_array($this->column) ? current($this->column) : $this->column;

        return Admin::translateField($column);
    }

    /**
     * Format the name of the field.
     *
     * @param string $column
     *
     * @return array|mixed|string
     */
    protected function formatName($column)
    {
        if (is_string($column)) {
            $name = explode('.', $column);

            if (count($name) == 1) {
                return $name[0];
            }

            $html = array_shift($name);
            foreach ($name as $piece) {
                $html .= "[$piece]";
            }

            return $html;
        }

        if (is_array($column)) {
            $names = [];
            foreach ($column as $key => $name) {
                $names[$key] = $this->formatName($name);
            }

            return $names;
        }

        return '';
    }

    /**
     * Set form element name.
     *
     * @param string $name
     *
     * @return $this
     *
     * @author Edwin Hui
     */
    public function setElementName($name)
    {
        $this->elementName = $name;

        return $this;
    }

    /**
     * Fill data to the field.
     *
     * @param array $data
     *
     * @return void
     */
    public function fill($data)
    {
        // Field value is already setted.
//        if (!is_null($this->value)) {
//            return;
//        }
        $this->row = new Collection($data);

        if (is_array($this->column)) {
            foreach ($this->column as $key => $column) {
                $this->value[$key] = array_get($data, $column);
            }
        } else {
            $this->value = array_get($data, $this->column);
        }

        if (isset($this->customFormat) && $this->customFormat instanceof \Closure) {
            $this->value = call_user_func($this->customFormat, new Collection($this->row));
        }
    }

    /**
     * custom format form column data when edit.
     *
     * @param \Closure $call
     * @return $this
     */
    public function customFormat(\Closure $call)
    {
        $this->customFormat = $call;

        return $this;
    }

    /**
     * Set original value to the field.
     *
     * @param array $data
     *
     * @return void
     */
    public function setOriginal(array $data)
    {
        if (is_array($this->column)) {
            foreach ($this->column as $key => $column) {
                $this->original[$key] = array_get($data, $column);
            }

            return;
        }

        $this->original = array_get($data, $this->column);
    }

    /**
     * 判断是否需要返回original字段值
     *
     * @return bool
     */
    public function needOriginal()
    {
        return $this->needOriginal;
    }

    /**
     * @param Form $form
     *
     * @return $this
     */
    public function setForm(Form $form = null)
    {
        $this->form = $form;
        $this->style($form->style());

        return $this;
    }

    /**
     * Set width for field and label.
     *
     * @param int $field
     * @param int $label
     *
     * @return $this
     */
    public function width(int $field = 8, int $label = 2)
    {
        $this->width = [
            'label' => $label,
            'field' => $field,
            'row'   => $field
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set the field options.
     *
     * @param array $options
     *
     * @return $this
     */
    public function options($options = [])
    {
        if ($options instanceof Arrayable) {
            $options = $options->toArray();
        }

        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * Set the field option checked.
     *
     * @param array $checked
     *
     * @return $this
     */
    public function checked($checked = [])
    {
        if ($checked instanceof Arrayable) {
            $checked = $checked->toArray();
        }

        $this->checked = array_merge($this->checked, $checked);

        return $this;
    }

    /**
     * Get or set rules.
     *
     * @param null  $rules
     * @param array $messages
     *
     * @return $this
     */
    public function rules($rules = null, $messages = [])
    {
        if ($rules instanceof \Closure) {
            $this->rules = $rules;
        }

        if (is_array($rules)) {
            $thisRuleArr = array_filter(explode('|', $this->rules));

            $this->rules = array_merge($thisRuleArr, $rules);
        } elseif (is_string($rules)) {
            $rules = array_filter(explode('|', "{$this->rules}|$rules"));

            $this->rules = implode('|', $rules);
        }

        $this->validationMessages = $messages;

        return $this;
    }

    /**
     * Get field validation rules.
     *
     * @return string
     */
    protected function getRules()
    {
        if ($this->rules instanceof \Closure) {
            return $this->rules->call($this, $this->form);
        }

        return $this->rules;
    }

    /**
     * Remove a specific rule by keyword.
     *
     * @param string $rule
     *
     * @return void
     */
    protected function removeRule($rule)
    {
        if (!is_string($this->rules)) {
            return;
        }

        $pattern = "/{$rule}[^\|]?(\||$)/";
        $this->rules = preg_replace($pattern, '', $this->rules, -1);
    }


    /**
     * Get key for error message.
     *
     * @return string
     */
    public function getErrorKey()
    {
        return $this->errorKey ?: $this->column;
    }

    /**
     * Set key for error message.
     *
     * @param string $key
     *
     * @return $this
     */
    public function setErrorKey($key)
    {
        $this->errorKey = $key;

        return $this;
    }

    /**
     * Set or get value of the field.
     *
     * @param null $value
     *
     * @return mixed
     */
    public function value($value = null)
    {
        if (is_null($value)) {
            return is_null($this->value) ? $this->getDefault() : $this->value;
        }

        $this->value = $value;

        return $this;
    }

    /**
     * Set default value for field.
     *
     * @param $default
     *
     * @return $this
     */
    public function default($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Get default value.
     *
     * @return mixed
     */
    public function getDefault()
    {
        if ($this->default instanceof \Closure) {
            return call_user_func($this->default, $this->form);
        }

        return $this->default;
    }

    /**
     * Set help block for current field.
     *
     * @param string $text
     * @param string $icon
     *
     * @return $this
     */
    public function help($text = '', $icon = 'fa-info-circle')
    {
        $this->help = compact('text', 'icon');

        return $this;
    }

    /**
     * Get column of the field.
     *
     * @return string|array
     */
    public function column()
    {
        return $this->column;
    }

    /**
     * Get label of the field.
     *
     * @return string
     */
    public function label()
    {
        return $this->label;
    }

    /**
     * Get original value of the field.
     *
     * @return mixed
     */
    public function original()
    {
        return $this->original;
    }

    /**
     *
     * @param array $input
     *
     * @return array|false
     */
    public function getValidatorData(array &$input)
    {
        $rules = $attributes = [];

        if (!$fieldRules = $this->getRules()) {
            return false;
        }

        if (is_string($this->column)) {
            if (!array_has($input, $this->column)) {
                return false;
            }

            $input = $this->sanitizeInput($input, $this->column);

            $rules[$this->column] = $fieldRules;
            $attributes[$this->column] = $this->label;
        }

        if (is_array($this->column)) {
            foreach ($this->column as $key => $column) {
                if (!array_key_exists($column, $input)) {
                    continue;
                }
                $input[$column.$key] = array_get($input, $column);
                $rules[$column.$key] = $fieldRules;

                $label = Admin::translateField($column);
                $attributes[$column.$key] = "{$this->label}[$label]";
            }
        }

        return [&$rules, &$this->validationMessages, &$attributes];
    }

    /**
     * Sanitize input data.
     *
     * @param array  $input
     * @param string $column
     *
     * @return array
     */
    protected function sanitizeInput($input, $column)
    {
        if ($this instanceof Field\MultipleSelect) {
            $value = array_get($input, $column);
            array_set($input, $column, array_filter($value));
        }

        return $input;
    }

    /**
     * Add html attributes to elements.
     *
     * @param array|string $attribute
     * @param mixed        $value
     *
     * @return $this
     */
    public function attribute($attribute, $value = null)
    {
        if (is_array($attribute)) {
            $this->attributes = array_merge($this->attributes, $attribute);
        } else {
            $this->attributes[$attribute] = (string) $value;
        }

        return $this;
    }

    /**
     * Set the field automatically get focus.
     *
     * @return Field
     */
    public function autofocus()
    {
        return $this->attribute('autofocus', true);
    }

    /**
     * Set the field as readonly mode.
     *
     * @return Field
     */
    public function readOnly()
    {
        return $this->attribute('readonly', true);
    }

    /**
     * Set field as disabled.
     *
     * @return Field
     */
    public function disable()
    {
        return $this->attribute('disabled', true);
    }

    /**
     * Set field placeholder.
     *
     * @param string $placeholder
     *
     * @return Field
     */
    public function placeholder($placeholder = '')
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Get placeholder.
     *
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder ?: t('Input', 'admin').' '.$this->label;
    }

    /**
     * 新增或修改之前对数据进行加工处理
     * 返回null则会跳过此字段
     *
     * @param $value
     * @return mixed
     */
    public function prepare($value)
    {
        return $value;
    }

    /**
     * Format the field attributes.
     *
     * @return string
     */
    protected function formatAttributes()
    {
        $html = [];

        foreach ($this->attributes as $name => $value) {
            $html[] = $name.'="'.e($value).'"';
        }

        return implode(' ', $html);
    }

    /**
     * @return $this
     */
    public function disableHorizontal()
    {
        $this->horizontal = false;

        return $this;
    }

    /**
     * @return array
     */
    public function getViewElementClasses()
    {
        if ($this->horizontal) {
            return [
                'label'      => "col-sm-{$this->width['label']} {$this->getLabelClass()}",
                'field'      => "col-sm-{$this->width['field']}",
                'form-group' => 'form-group ',
            ];
        }

        return ['label' => "{$this->getLabelClass()}", 'field' => '', 'form-group' => ''];
    }

    /**
     * Set form element class.
     *
     * @param string|array $class
     *
     * @return $this
     */
    public function setElementClass($class)
    {
        $this->elementClass = (array) $class;

        return $this;
    }

    /**
     * Get element class.
     *
     * @return array
     */
    protected function getElementClass()
    {
        if (!$this->elementClass) {
            $name = $this->elementName ?: $this->formatName($this->column);

            $this->elementClass = (array) str_replace(['[', ']'], '_', $name);
        }

        return $this->elementClass;
    }

    /**
     * Get element class string.
     *
     * @return mixed
     */
    protected function getElementClassString()
    {
        $elementClass = $this->getElementClass();

        if (Arr::isAssoc($elementClass)) {
            $classes = [];

            foreach ($elementClass as $index => $class) {
                $classes[$index] = is_array($class) ? implode(' ', $class) : $class;
            }

            return $classes;
        }

        return $this->classPrefix.implode(' ', $elementClass);
    }

    /**
     * Get element class selector.
     *
     * @return string|array
     */
    protected function getElementClassSelector()
    {
        $elementClass = $this->getElementClass();

        if (Arr::isAssoc($elementClass)) {
            $classes = [];

            foreach ($elementClass as $index => $class) {
                $classes[$index] = '.'.(is_array($class) ? implode('.', $class) : $class);
            }

            return $classes;
        }

        return '.'.$this->classPrefix.implode('.', $elementClass);
    }

    /**
     * Add the element class.
     *
     * @param $class
     *
     * @return $this
     */
    public function addElementClass($class)
    {
        if (is_array($class) || is_string($class)) {
            $this->elementClass = array_merge($this->elementClass, (array) $class);

            $this->elementClass = array_unique($this->elementClass);
        }

        return $this;
    }

    /**
     * Remove element class.
     *
     * @param $class
     *
     * @return $this
     */
    public function removeElementClass($class)
    {
        $delClass = [];

        if (is_string($class) || is_array($class)) {
            $delClass = (array) $class;
        }

        foreach ($delClass as $del) {
            if (($key = array_search($del, $this->elementClass))) {
                unset($this->elementClass[$key]);
            }
        }

        return $this;
    }

    /**
     *
     * @param string $style
     * @return $this
     */
    public function style(string $style)
    {
        $this->style = $style;

        $style && $this->disableHorizontal();

        return $this;
    }

    /**
     * Add variables to field view.
     *
     * @param array $variables
     *
     * @return $this
     */
    protected function addVariables(array $variables = [])
    {
        $this->variables = array_merge($this->variables, $variables);

        return $this;
    }

    /**
     * @return string
     */
    public function getLabelClass(): string
    {
        return $this->classPrefix.implode(' ', $this->labelClass);
    }

    /**
     * @param array $labelClass
     *
     * @return self
     */
    public function setLabelClass(array $labelClass)
    : self
    {
        $this->labelClass = $labelClass;

        return $this;
    }

    /**
     * Get the view variables of this field.
     *
     * @return array
     */
    public function variables()
    {
        return array_merge($this->variables, [
            'id'          => $this->id,
            'name'        => $this->elementName ?: $this->formatName($this->column),
            'help'        => $this->help,
            'class'       => $this->getElementClassString(),
            'value'       => $this->value(),
            'label'       => $this->label ?: $this->column,
            'viewClass'   => $this->getViewElementClasses(),
            'column'      => $this->column,
            'errorKey'    => $this->getErrorKey(),
            'attributes'  => $this->formatAttributes(),
            'placeholder' => $this->getPlaceholder(),
            'errors'      => get_flash_errors(),
        ]);
    }

    /**
     * Get view of this field.
     *
     * @return string
     */
    public function getView()
    {
        if (!empty($this->view)) {
            $view = $this->view;
        } else {
            $class = explode('\\', get_called_class());

            $view = 'admin::form.'.strtolower(end($class));
        }

        if (strpos($view, 'admin::') !== 0) {
            return $view;
        }

        // 增加style前缀目录的方法只针对系统内置模板
        $view = explode('.', $view);
        $view[0] = $this->style ? "{$view[0]}.{$this->style}" : $view[0];

        return implode('.', $view);
    }

    /**
     * Get script of current field.
     *
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * If this field should render.
     *
     * @return bool
     */
    protected function shouldRender()
    {
        if (!$this->display) {
            return false;
        }

        return true;
    }

    /**
     * Render this filed.
     *
     * @return string
     */
    public function render()
    {
        if (!$this->shouldRender()) {
            return '';
        }

        if ($this->script) {
            Admin::script($this->script);
        }

        return blade($this->getView(), $this->variables())->render();
    }

}
