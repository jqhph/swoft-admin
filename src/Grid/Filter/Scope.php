<?php

namespace Swoft\Admin\Grid\Filter;

use Swoft\Support\Collection;
use Swoft\Support\Str;
use Swoft\Core\RequestContext;
use Swoft\Support\Contracts\Renderable;
use Swoft\Support\Url;
use Swoft\Db\QueryBuilder;

/**
 * @method QueryBuilder where(string $column, $value, $operator = QueryBuilder::OPERATOR_EQ, $connector = QueryBuilder::LOGICAL_AND)
 * @method QueryBuilder whereIn(string $column, array $values, string $connector = QueryBuilder::LOGICAL_AND)
 * @method QueryBuilder orWhere($column, $value, $operator = QueryBuilder::OPERATOR_EQ)
 * @method QueryBuilder openWhere($connector = QueryBuilder::LOGICAL_AND)
 * @method QueryBuilder closeWhere()
 * @method QueryBUilder whereNotIn(string $column, array $values, string $connector = QueryBuilder::LOGICAL_AND)
 * @method QueryBuilder whereBetween(string $column, $min, $max, string $connector = QueryBuilder::LOGICAL_AND)
 * @method QueryBuilder whereNotBetween(string $column, $min, $max, string $connector = QueryBuilder::LOGICAL_AND)
 */
class Scope implements Renderable
{
    const QUERY_NAME = '_s';
    const IGNORE_VALUE = '_';

    /**
     * @var Scopes
     */
    public $parent = '';

    /**
     * @var string
     */
    public $type = '';

    /**
     * @var string
     */
    public $key = '';

    /**
     * @var string
     */
    protected $label = '';

    /**
     * @var string
     */
    protected $queryColumn = '';

    /**
     * @var Collection
     */
    protected $queries;

    /**
     * Scope constructor.
     *
     * @param Scopes $scopes
     * @param string $key
     * @param string $label
     */
    public function __construct(Scopes $scopes, $key, string $label = '')
    {
        $this->parent = $scopes;
        $this->type = $scopes->getType();
        $this->key = (string)$key;
        $this->label = $label ? $label : Str::studly($key);
        $this->queryColumn = $this->buildQueryColumn();

        $this->queries = new Collection();
    }

    /**
     * Get label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * 判断是否选中条件
     *
     * @return bool
     */
    public function buildConditionable()
    {
        $value = RequestContext::getRequest()->query($this->queryColumn, $this->parent->getDefault());
        if ($value === static::IGNORE_VALUE) {
            return false;
        }

        return $value == $this->key;
    }

    /**
     * Get model query conditions.
     *
     * @return array
     */
    public function condition()
    {
        return $this->queries->map(function ($query) {
            return [$query['method'] => $query['arguments']];
        })->toArray();
    }

    /**
     * @return string
     */
    public function render()
    {
        $url = Url::query();

        $url->add([$this->queryColumn => $this->key]);
        $url->delete($this->parent->parent()->getModel()->getPageName());

        return "<li><a href=\"{$url->build()}\">{$this->label}</a></li>";
    }

    /**
     * 获取取消url
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return Url::full([$this->queryColumn => static::IGNORE_VALUE]);
    }

    /**
     * @return string
     */
    public function buildQueryColumn()
    {
        return static::QUERY_NAME.$this->type;
    }

    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return $this
     */
    public function __call($method, $arguments)
    {
        $this->queries->push(compact('method', 'arguments'));

        return $this;
    }
}
