<?php

namespace Swoft\Admin\Form;

use Swoft\Admin\Admin;
use Swoft\Admin\Form;
use Swoft\Admin\Form\MultipleForm;
use Swoft\Admin\Form\Field\Hidden;
use Swoft\Admin\Widgets\Card;
use Swoft\Support\Collection;
use Swoft\Support\Str;
use Swoft\Support\Url;

/**
 * Class Builder.
 */
class Builder
{
    /**
     *  Previous url key.
     */
    const PREVIOUS_URL_KEY = '_previous_';

    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var Collection
     */
    protected $fields;

    /**
     * @var Collection
     */
    protected $multipleFormFields = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Modes constants.
     */
    const MODE_EDIT = 'edit';
    const MODE_CREATE = 'create';

    /**
     * Form action mode, could be create|view|edit.
     *
     * @var string
     */
    protected $mode = 'create';

    /**
     * @var array
     */
    protected $hiddenFields = [];

    /**
     * @var Tools
     */
    protected $tools;

    /**
     * @var Footer
     */
    protected $footer;

    /**
     * 子表单
     *
     * @var MultipleForm[]
     */
    protected $multipleForms = [];

    /**
     * Width for label and field.
     *
     * @var array
     */
    protected $width = [
        'label' => 2,
        'field' => 8,
    ];

    /**
     * View for this form.
     *
     * @var string
     */
    protected $view = 'admin::form';

    /**
     * Form title.
     *
     * @var string
     */
    protected $title;

    /**
     * @var Layout
     */
    protected $layout;

    /**
     * 默认列内容宽度
     *
     * @var int
     */
    protected $defaultColumnWidth = 12;

    /**
     * Builder constructor.
     *
     * @param Form $form
     */
    public function __construct(Form $form)
    {
        $this->form = $form;

        $this->layout = new Layout($form);
        $this->fields = new Collection();

        $this->multipleFormFields = new Collection();

        $this->init();
    }

    /**
     * Do initialize.
     */
    public function init()
    {
        $this->tools = new Tools($this);
        $this->footer = new Footer($this);
        // 默认设置为创建模式
        $this->setMode(static::MODE_CREATE);
    }

    /**
     * Get form tools instance.
     *
     * @return Tools
     */
    public function getTools()
    {
        return $this->tools;
    }

    /**
     * Get form footer instance.
     *
     * @return Footer
     */
    public function getFooter()
    {
        return $this->footer;
    }

    /**
     * Set the builder mode.
     *
     * @param string $mode
     *
     * @return void
     */
    public function setMode($mode = 'create')
    {
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Returns builder is $mode.
     *
     * @param $mode
     *
     * @return bool
     */
    public function isMode($mode)
    {
        return $this->mode == $mode;
    }

    /**
     * Set resource Id.
     *
     * @param $id
     *
     * @return void
     */
    public function setResourceId($id)
    {
        $this->id = $id;
    }

    /**
     * Get Resource id.
     *
     * @return mixed
     */
    public function getResourceId()
    {
        return $this->id;
    }

    /**
     * @param Field $field
     * @return $this
     */
    public function pushField(Field $field)
    {
        $this->fields->push($field);
        return $this;
    }

    /**
     * @param int $field
     * @param int $label
     *
     * @return $this
     */
    public function setWidth($field = 8, $label = 2)
    {
        $this->width = [
            'label' => $label,
            'field' => $field,
        ];

        return $this;
    }

    /**
     * Get label and field width.
     *
     * @return array
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set form action.
     *
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Get Form action.
     *
     * @return string
     */
    public function getAction()
    {
        if ($this->action) {
            return $this->action;
        }

        if ($this->isMode(static::MODE_EDIT)) {
            return Admin::url()->update($this->id);
        }

        if ($this->isMode(static::MODE_CREATE)) {
            return Admin::url()->create();
        }
        return '';
    }

    /**
     * Set view for this form.
     *
     * @param string $view
     *
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Set title for form.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param Field $field
     * @return $this
     */
    public function addMultipleFormField(Field $field)
    {
        $this->multipleFormFields[] = $field;

        return $this;
    }

    /**
     * Get fields of this builder.
     *
     * @return Collection
     */
    public function fields()
    {
        return $this->fields->merge($this->multipleFormFields);
    }

    /**
     * Get specify field.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function field($name)
    {
        return $this->fields()->first(function (Field $field) use ($name) {
            return $field->column() == $name;
        });
    }

    /**
     * If the parant form has rows.
     *
     * @return bool
     */
    public function hasRows()
    {
        return !empty($this->form->rows);
    }

    /**
     * Get field rows of form.
     *
     * @return array
     */
    public function getRows()
    {
        return $this->form->rows;
    }

    /**
     * @return array
     */
    public function getHiddenFields()
    {
        return $this->hiddenFields;
    }

    /**
     * @param Field $field
     *
     * @return void
     */
    public function addHiddenField(Field $field)
    {
        $this->hiddenFields[] = $field;
    }

    /**
     * Add or get options.
     *
     * @param array $options
     *
     * @return array|null
     */
    public function options($options = [])
    {
        if (empty($options)) {
            return $this->options;
        }

        $this->options = array_merge($this->options, $options);
    }

    /**
     * Get or set option.
     *
     * @param string $option
     * @param mixed  $value
     *
     * @return $this
     */
    public function option($option, $value = null)
    {
        if (func_num_args() == 1) {
            return array_get($this->options, $option);
        }

        $this->options[$option] = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function title()
    {
        if ($this->title) {
            return $this->title;
        }

        if ($this->mode == static::MODE_CREATE) {
            return Admin::translateLabel('Create');
        }

        if ($this->mode == static::MODE_EDIT) {
            return Admin::translateLabel('Edit');
        }

        return '';
    }

    /**
     * Determine if form fields has files.
     *
     * @return bool
     */
    public function hasFile()
    {
        foreach ($this->fields() as $field) {
            if ($field instanceof Field\File || $field instanceof Field\MultipleFile) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add field for store redirect url after update or store.
     *
     * @return void
     */
    protected function addRedirectUrlField()
    {
        $previous = Url::previous();

        if (!$previous || $previous == Url::current()) {
            return;
        }

        if (Str::contains($previous, Url::to(Url::current()))) {
            $this->addHiddenField((new Hidden(static::PREVIOUS_URL_KEY))->value($previous));
        }
    }

    /**
     * Open up a new HTML form.
     *
     * @param array $options
     *
     * @return string
     */
    public function open($options = [])
    {
        $attributes = [];

        if ($this->isMode(self::MODE_EDIT)) {
            $this->addHiddenField((new Hidden('_method'))->value('PUT'));
        }

        $this->addRedirectUrlField();

        $attributes['action'] = $this->getAction();
        $attributes['method'] = array_get($options, 'method', 'post');
        $attributes['accept-charset'] = 'UTF-8';

        $attributes['class'] = array_get($options, 'class');

        if ($this->hasFile()) {
            $attributes['enctype'] = 'multipart/form-data';
        }

        $html = [];
        foreach ($attributes as $name => $value) {
            $html[] = "$name=\"$value\"";
        }

        return '<form '.implode(' ', $html).' pjax-container>';
    }

    /**
     * Close the current form.
     *
     * @return string
     */
    public function close()
    {
        $this->form = null;
        $this->fields = null;

        return '</form>';
    }

    /**
     * 布局
     *
     * @param \Closure $closure
     * @return Layout
     */
    public function layout($closure = null)
    {
        if ($closure) {
            $closure($this->layout);
        }

        return $this->layout;
    }

    /**
     * 设置列宽度
     *
     * @param int $width
     * @return $this
     */
    public function setDefaultColumnWidth(int $width)
    {
        $this->defaultColumnWidth = $width;

        return $this;
    }

    /**
     * 增加子表单
     *
     * @param MultipleForm $form
     */
    public function addForm(MultipleForm $form)
    {
        $this->multipleForms[] = $form;

        $form->style($this->form->style());

        $form->disableReset();
        $form->disableSubmit();
        $form->disableFormTag();

        return $this;
    }

    /**
     * Render form header tools.
     *
     * @return string
     */
    public function renderTools()
    {
        return $this->tools->render();
    }

    /**
     * Render form footer.
     *
     * @return string
     */
    public function renderFooter()
    {
        return $this->footer->render();
    }

    /**
     * @return string
     */
    public function renderFields()
    {
        if ($style = $this->form->style()) {
            foreach ($this->fields as $field) {
                $field->style($style);
            }

            return blade('admin::form.'.$style, ['fields' => $this->fields])->render();
        }
        $html = '';

        foreach ($this->fields as $field) {
            $html .= $field->render();
        }
        return $html;
    }

    /**
     * Render form.
     *
     * @return string
     */
    public function render()
    {
        $tabObj = $this->form->getTab();

        if (!$tabObj->isEmpty()) {
            $script = <<<'SCRIPT'
var hash = document.location.hash;
if (hash) {
    $('.nav-tabs a[href="' + hash + '"]').tab('show');
}
// Change hash for page-reload
$('.nav-tabs a').on('shown.bs.tab', function (e) {
    history.pushState(null,null, e.target.hash);
});
if ($('.has-error').length) {
    $('.has-error').each(function () {
        var tabId = '#'+$(this).closest('.tab-pane').attr('id');
        $('li a[href="'+tabId+'"] i').removeClass('hide');
    });
    var first = $('.has-error:first').closest('.tab-pane').attr('id');
    $('li a[href="#'+first+'"]').tab('show');
}
SCRIPT;
            Admin::script($script);
        }

        $data = [
            'form'   => $this,
            'tabObj' => $this->renderTab($tabObj),
            'width'  => $this->width,
        ];

        // 默认卡片
        $card = (new Card($this->title(), blade($this->view, $data)))
            ->tool($this->renderTools());

        $this->layout->prepend($this->defaultColumnWidth, $card);

        return <<<EOF
{$this->open(['class' => "form-horizontal"])}
    {$this->layout->build()}
{$this->close()}
EOF;

    }

    /**
     * @return string
     */
    protected function renderTab($tab)
    {
        if ($tab->isEmpty()) {
            return '';
        }

        if ($this->form->style() == Form::STYLE_ROW) {
            return blade('admin::form.row.tab', ['tabObj' => $tab]);
        }

        return blade('admin::form.tab', ['tabObj' => $tab]);
    }
}
