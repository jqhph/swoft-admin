<?php

namespace Swoft\Admin;

use Swoft\Admin\Show\Divider;
use Swoft\Admin\Show\Field;
use Swoft\Admin\Show\Panel;
use Swoft\Db\Model;
use Swoft\Support\Arr;
use Swoft\Support\Collection;
use Swoft\Support\Contracts\Renderable;

class Show implements Renderable
{
    /**
     * @var mixed
     */
    protected $primaryKey;

    /**
     * The data to show.
     *
     * @var Collection
     */
    protected $data;

    /**
     * Show panel builder.
     *
     * @var callable
     */
    protected $builder;

    /**
     * Resource path for this show page.
     *
     * @var string
     */
    protected $resource;

    /**
     * Fields to be show.
     *
     * @var Collection
     */
    protected $fields;

    /**
     * Relations to be show.
     *
     * @var Collection
     */
    protected $relations;

    /**
     * @var Panel
     */
    protected $panel;

    /**
     * If show contents in box.
     *
     * @var bool
     */
    protected $wrapped = false;

    public function __construct($id, $builder = null)
    {
        $this->builder = $builder;

        $this->primaryKey = $id;

        $this->initPanel();
        $this->initContents();
    }

    /**
     * 获取主键
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->primaryKey;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = new Collection($data);
        return $this;
    }

    /**
     * @return Collection
     */
    public function getData()
    {
        return $this->data ?: new Collection();
    }

    /**
     * Initialize the contents to show.
     */
    protected function initContents()
    {
        $this->fields = new Collection();
        $this->relations = new Collection();

        $this->setData(Admin::repository()->findForView($this));
    }

    /**
     * Initialize panel.
     */
    protected function initPanel()
    {
        $this->panel = new Panel($this);
    }

    /**
     * Get panel instance.
     *
     * @return Panel
     */
    public function panel()
    {
        return $this->panel;
    }

    /**
     * Add a model field to show.
     *
     * @param string $name
     * @param string $label
     *
     * @return Field
     */
    public function field($name, $label = '')
    {
        return $this->addField($name, $label);
    }

    /**
     * 使用格子包装内容
     *
     * @return $this
     */
    public function wrap()
    {
        $this->wrapped = true;

        $this->fields->map(function ($field) {
            $field->wrap();
        });

        return $this;
    }

    /**
     * Add multiple fields.
     *
     * @param array $fields
     *
     * @return $this
     */
    public function fields(array $fields = [])
    {
        if (!Arr::isAssoc($fields)) {
            $fields = array_combine($fields, $fields);
        }

        foreach ($fields as $field => $label) {
            $this->field($field, $label);
        }

        return $this;
    }

    /**
     * Show all fields.
     *
     * @return Show
     */
    public function all()
    {
        $fields = array_keys($this->data->toArray());

        return $this->fields($fields);
    }

    /**
     * Add a model field to show.
     *
     * @param string $name
     * @param string $label
     *
     * @return Field
     */
    protected function addField($name, $label = '')
    {
        $field = new Field($name, $label);

        $field->setParent($this);

        $this->overwriteExistingField($name);

        $this->wrapped && $field->wrap();

        return tap($field, function ($field) {
            $this->fields->push($field);
        });
    }

    /**
     * Overwrite existing field.
     *
     * @param string $name
     */
    protected function overwriteExistingField($name)
    {
        if ($this->fields->isEmpty()) {
            return;
        }

        $this->fields = $this->fields->filter(
            function (Field $field) use ($name) {
                return $field->getName() != $name;
            }
        );
    }

    /**
     * Show a divider.
     */
    public function divider(bool $showLine = true)
    {
        $this->fields->push(new Divider($showLine));
    }

    /**
     * Set resource path.
     *
     * @param string $resource
     *
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Handle model field.
     *
     * @param string $method
     * @param string $label
     *
     * @return bool|Field
     */
    protected function handleModelField($method, $label)
    {
        if (in_array($method, $this->data->toArray())) {
            return $this->addField($method, $label);
        }

        return false;
    }


    /**
     * Render the show panels.
     *
     * @return string
     */
    public function render()
    {
        if (is_callable($this->builder)) {
            call_user_func($this->builder, $this);
        }

        if ($this->fields->isEmpty()) {
            $this->all();
        }

        if (is_array($this->builder)) {
            $this->fields($this->builder);
        }

        $this->fields->each(function (Field $field) {
            $field->setValue($this->data);
        });

        $data = [
            'panel' => $this->panel->fill($this->fields),
        ];

        return blade('admin::show', $data)->render();
    }

    /**
     * 添加字段
     *
     * @param string $name
     * @return Field
     */
    public function __get($name)
    {
        return $this->field($name);
    }
}
