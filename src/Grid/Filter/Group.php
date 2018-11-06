<?php

namespace Swoft\Admin\Grid\Filter;

use Swoft\Admin\Admin;
use Swoft\Support\Collection;

class Group extends AbstractFilter
{
    /**
     * @var \Closure|null
     */
    protected $builder;

    /**
     * @var string
     */
    protected $name;

    /**
     * Input value from presenter.
     *
     * @var mixed
     */
    public $input;

    /**
     * Group constructor.
     *
     * @param string          $column
     * @param string|callable $label
     * @param \Closure|null   $builder
     */
    public function __construct($column, $label = '', \Closure $builder = null)
    {
        $this->column = $column;

        if (is_callable($label) && is_null($builder)) {
            $this->builder = $label;
            $this->label = Admin::translateField($this->column);
        } elseif (is_string($label) && is_callable($builder)) {
            $this->label = $label;
            $this->builder = $builder;
        }

        $this->initialize();

        if ($this->builder) {
            call_user_func($this->builder, $this);
        }
    }

    /**
     * Initialize a group filter.
     */
    protected function initialize()
    {
        $this->id = $this->formatId($this->column);
        $this->group = new Collection();
        $this->name = "{$this->id}-filter-group";

        $this->setupDefaultPresenter();
    }

    /**
     * Join a query to group.
     *
     * @param string $label
     * @param array  $condition
     *
     * @return $this
     */
    protected function joinGroup($label, array $condition)
    {
        $label = t($label, 'admin.filters');

        $this->group->push(
            compact('label', 'condition')
        );

        return $this;
    }

    /**
     * Filter out `equal` records.
     *
     * @param string $label
     * @param string $operator
     *
     * @return Group
     */
    public function equal($label = '', $operator = '=')
    {
        $label = $label ?: $operator;

        $condition = [$this->column, $this->value, $operator];

        return $this->joinGroup($label, $condition);
    }

    /**
     * Filter out `not equal` records.
     *
     * @param string $label
     *
     * @return Group
     */
    public function notEqual($label = '')
    {
        return $this->equal($label, '!=');
    }

    /**
     * Filter out `greater then` records.
     *
     * @param string $label
     *
     * @return Group
     */
    public function gt($label = '')
    {
        return $this->equal($label, '>');
    }

    /**
     * Filter out `less then` records.
     *
     * @param string $label
     *
     * @return Group
     */
    public function lt($label = '')
    {
        return $this->equal($label, '<');
    }

    /**
     * Filter out `not less then` records.
     *
     * @param string $label
     *
     * @return Group
     */
    public function nlt($label = '')
    {
        return $this->equal($label, '>=');
    }

    /**
     * Filter out `not greater than` records.
     *
     * @param string $label
     *
     * @return Group
     */
    public function ngt($label = '')
    {
        return $this->equal($label, '<=');
    }

    /**
     * Filter out records that match the regex.
     *
     * @param string $label
     *
     * @return Group
     */
    public function match($label = '')
    {
        $label = $label ?: 'Match';

        return $this->equal($label, 'REGEXP');
    }

    /**
     * Specify a where query.
     *
     * @param string   $label
     * @param \Closure $builder
     *
     * @return Group
     */
    public function where($label, \Closure $builder)
    {
        $this->input = $this->value;

        $condition = [$builder->bindTo($this)];

        return $this->joinGroup($label, $condition);
    }

    /**
     * Specify a where like query.
     *
     * @param string $label
     * @param string $operator
     *
     * @return Group
     */
    public function like($label = '', $operator = 'like')
    {
        $label = $label ?: $operator;

        $condition = [$this->column, "%{$this->value}%", $operator];

        return $this->joinGroup($label, $condition);
    }

    /**
     * Alias of `like` method.
     *
     * @param string $label
     *
     * @return Group
     */
    public function contains($label = '')
    {
        return $this->like($label);
    }

    /**
     * Specify a where ilike query.
     *
     * @param string $label
     *
     * @return Group
     */
    public function ilike($label = '')
    {
        return $this->like($label, 'ilike');
    }

    /**
     * Filter out records which starts with input query.
     *
     * @param string $label
     *
     * @return Group
     */
    public function startWith($label = '')
    {
        $label = $label ?: 'Start with';

        $condition = [$this->column, "{$this->value}%", 'like'];

        return $this->joinGroup($label, $condition);
    }

    /**
     * Filter out records which ends with input query.
     *
     * @param string $label
     *
     * @return Group
     */
    public function endWith($label = '')
    {
        $label = $label ?: 'End with';

        $condition = [$this->column, "%{$this->value}", 'like'];

        return $this->joinGroup($label, $condition);
    }

    /**
     * @return string
     */
    public function getGroupTypeInputName()
    {
        return "{$this->id}_group";
    }

    /**
     * {@inheritdoc}
     */
    public function condition($inputs)
    {
        $value = trim(array_get($inputs, $this->column));

        if (!isset($value) || $value === '') {
            return $this->buildDefaultCondition();
        }

        $this->value = $value;

        $group = array_get($inputs, $this->getGroupTypeInputName());

        $this->rebuildGroup();

        if ($query = $this->group->get($group)) {
            return $this->buildCondition(...$query['condition']);
        }
    }

    protected function rebuildGroup()
    {
        if ($this->builder) {
            $this->group = new Collection();
            call_user_func($this->builder, $this);
        }
    }

    protected function buildDefaultCondition()
    {
        if ($this->defaultValue === null) {
            return;
        }

        $this->value = $this->defaultValue;

        $this->rebuildGroup();

        if ($query = $this->group->first()) {
            return $this->buildCondition(...$query['condition']);
        }
    }

    /**
     * Inject script to current page.
     */
    protected function injectScript()
    {
        $script = <<<SCRIPT
$(".{$this->name} li a").click(function(){
    $(".{$this->name}-label").text($(this).text());
    $(".{$this->name}-operation").val($(this).data('index'));
});
SCRIPT;

        Admin::script($script);
    }

    /**
     * {@inheritdoc}
     */
    public function variables()
    {
        $select = http_input($this->getGroupTypeInputName());

        $default = $this->group->get($select) ?: $this->group->first();

        return array_merge(parent::variables(), [
            'group_name' => $this->name,
            'default'    => $default,
            'groupvalue' => $select ?: 0,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $this->injectScript();

        return parent::render();
    }
}
