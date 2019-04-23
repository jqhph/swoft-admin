<?php

namespace Swoft\Admin\Grid\Filter;

class Where extends AbstractFilter
{
    /**
     * Query closure.
     *
     * @var \Closure
     */
    protected $where;

    /**
     * Input value from presenter.
     *
     * @var mixed
     */
    public $input;

    /**
     * Where constructor.
     *
     */
    public function __construct($label, \Closure $query)
    {
        $this->where = $query;

        $this->label = $this->formatLabel($label);
        $this->column = $label ?: static::getQueryHash($query, $this->label);
        $this->id = $this->formatId($this->column);

        $this->setupDefaultPresenter();
    }

    /**
     * Get the hash string of query closure.
     *
     * @param \Closure $closure
     * @param string   $label
     *
     * @return string
     */
    public static function getQueryHash(\Closure $closure, $label = '')
    {
        $reflection = new \ReflectionFunction($closure);

        return md5($reflection->getFileName().$reflection->getStartLine().$reflection->getEndLine().$label);
    }

    /**
     * Get condition of this filter.
     *
     * @param array $inputs
     *
     * @return array|mixed|void
     */
    public function condition($inputs)
    {
        $value = array_get($inputs, $this->column ?: static::getQueryHash($this->where, $this->label));

        if (is_null($value)) {
            return;
        }

        $this->input = $this->value = $value;

        return $this->buildCondition(...call_user_func($this->where, $value));
    }
}
