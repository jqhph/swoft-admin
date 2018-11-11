<?php

namespace Swoft\Admin\Grid;

use Swoft\Admin\Admin;
use Swoft\Admin\Grid\Filter\AbstractFilter;
use Swoft\Admin\Grid\Filter\Layout\Layout;
use Swoft\Admin\Grid\Filter\Scope;
use Swoft\Admin\Grid\Filter\Scopes;
use Swoft\Core\RequestContext;
use Swoft\Support\Collection;
use Swoft\Admin\Grid\Filter\Between;
use Swoft\Admin\Grid\Filter\Equal;
use Swoft\Admin\Grid\Filter\NotEqual;
use Swoft\Admin\Grid\Filter\Like;
use Swoft\Admin\Grid\Filter\Ilike;
use Swoft\Admin\Grid\Filter\Gt;
use Swoft\Admin\Grid\Filter\Lt;
use Swoft\Admin\Grid\Filter\In;
use Swoft\Admin\Grid\Filter\NotIn;
use Swoft\Admin\Grid\Filter\Where;
use Swoft\Admin\Grid\Filter\Hidden;
use Swoft\Admin\Grid\Filter\Group;
use Swoft\Support\Url;

/**
 * Class Filter.
 *
 * @method Equal    equal($column, $label = '')
 * @method NotEqual notEqual($column, $label = '')
 * @method Like     like($column, $label = '')
 * @method Ilike    ilike($column, $label = '')
 * @method Gt       gt($column, $label = '')
 * @method Lt       lt($column, $label = '')
 * @method Between  between($column, $label = '')
 * @method In       in($column, $label = '')
 * @method NotIn    notIn($column, $label = '')
 * @method Where    where($label, $callback)
 * @method Hidden   hidden($name, $value)
 * @method void     group($column, $labelOrBuilder = '', $builder = null)
 */
class Filter
{
    /**
     * @var string
     */
    public static $ignoreValue = '-_-';

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var array
     */
    protected $supports = [
        'equal', 'notEqual', 'ilike', 'like', 'gt', 'lt', 'between', 'group',
        'where', 'in', 'notIn', 'date', 'day', 'month', 'year', 'hidden',
    ];

    /**
     * Action of search form.
     *
     * @var string
     */
    protected $action;

    /**
     * @var string
     */
    protected $view = 'admin::filter.container';

    /**
     * @var string
     */
    protected $filterID = 'filter-box';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var bool
     */
    public $expand = false;

    /**
     * @var Collection
     */
    protected $scopes;

    /**
     * @var Layout
     */
    protected $layout;

    /**
     * Create a new filter instance.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;

        $this->initLayout();

        $this->scopes = new Collection();
    }

    /**
     * Initialize filter layout.
     */
    protected function initLayout()
    {
        $this->layout = new Filter\Layout\Layout($this);
    }

    /**
     * @return Layout
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Set action of search form.
     *
     * @param string $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get grid model.
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set ID of search form.
     *
     * @param string $filterID
     *
     * @return $this
     */
    public function setFilterID($filterID)
    {
        $this->filterID = $filterID;

        return $this;
    }

    /**
     * Get filter ID.
     *
     * @return string
     */
    public function getFilterID()
    {
        return $this->filterID;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        $this->setFilterID("{$this->name}-{$this->filterID}");

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get all conditions of the filters.
     *
     * @return array
     */
    public function conditions()
    {
        $req = RequestContext::getRequest();
        $inputs = array_merge($req->query(), $req->post());

        $conditions = [];
        foreach ($this->filters() as $filter) {
            $conditions[] = $filter->condition($inputs);
        }

        $conditions = tap(array_filter($conditions), function ($conditions) {
            if (!empty($conditions)) {
                $this->expand();
            }
        });

        return $conditions;
    }

    /**
     * 获取没有查询字段的链接
     *
     * @return string
     */
    public function getWithoutQueriesUrl()
    {
        $query = Url::query();

        $deletes = [
            $this->getModel()->getPageName()
        ];
        foreach ($this->filters() as $filter) {
            $deletes[] = $filter->getColumn();

            if ($filter instanceof Group) {
                $deletes[] = $filter->getGroupTypeInputName();
            }
        }

        return $query->delete($deletes)->build();
    }

    /**
     * Add a filter to grid.
     *
     * @param AbstractFilter $filter
     *
     * @return AbstractFilter
     */
    protected function addFilter(AbstractFilter $filter)
    {
        $this->layout->addFilter($filter);

        $filter->setParent($this);

        return $this->filters[] = $filter;
    }

    /**
     * Use a custom filter.
     *
     * @param AbstractFilter $filter
     *
     * @return AbstractFilter
     */
    public function use(AbstractFilter $filter)
    {
        return $this->addFilter($filter);
    }

    /**
     * Get all filters.
     *
     * @return AbstractFilter[]
     */
    public function filters()
    {
        return $this->filters;
    }

    /**
     *
     * @param string $type
     * @return string
     */
    public function getScopeCurrentLabel(string $type)
    {
        if (!$this->scopes->has($type)) {
            return '';
        }
        /* @var Scope $scope */
        foreach ($this->scopes->get($type) as $scope) {
            if ($scope->buildConditionable()) {
                return $scope->getLabel();
            }
        }

        return $this->scopes->get($type)->getLabel();
    }

    /**
     * @param string $type
     * @return string
     */
    public function getScopeCurrentStyle(string $type)
    {
        if (!$this->scopes->has($type)) {
            return '';
        }

        return $this->scopes->get($type)->getStyle();

    }

    /**
     *
     * @param string $type
     * @param \Closure $callback
     * @return Scopes
     */
    public function scope(string $type, \Closure $callback)
    {
        $scopes = new Scopes($this, $type);
        $callback($scopes);

        $this->scopes->put($type, $scopes);

        return $scopes;
    }

    /**
     * Get all filter scopes.
     *
     * @return Collection
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Get scope conditions.
     *
     * @return array
     */
    protected function scopeConditions()
    {
        $conditions = [];

        /* @var Scope $scope */
        foreach ($this->scopes as $scopes) {
            foreach ($scopes as $scope) {
                if ($scope->buildConditionable()) {
                    $conditions = array_merge($conditions, $scope->condition());
                }
            }
        }

        return $conditions;
    }

    /**
     * Expand filter container.
     *
     * @return $this
     */
    public function expand()
    {
        $this->expand = true;

        return $this;
    }

    /**
     * Execute the filter with conditions.
     *
     * @param bool $toArray
     *
     * @return array|Collection|mixed
     */
    public function execute($toArray = true)
    {
        $conditions = array_merge(
            $this->conditions(),
            $this->scopeConditions()
        );

        return $this->model->addConditions($conditions)->buildData($toArray);
    }

    /**
     * 设置表单重置事件
     *
     * @param string $script
     * @return $this
     */
    public function onReset(string $script)
    {
        Admin::script(
            <<<EOF
var _filter = $('#{$this->filterID}');
_filter.find('button[type="reset"]').on('click',function(){setTimeout(function() {{$script}},20)});
EOF
        );
        return $this;
    }

    /**
     * Get the string contents of the filter view.
     *
     * @return string
     */
    public function render()
    {
        if (empty($this->filters)) {
            return '';
        }

        $this->onReset(<<<EOF
 _filter.find('input[type="text"]').val('');
 _filter.find('select').prop('selected', false);
EOF
);
        $reset = $this->getWithoutQueriesUrl();

        return blade($this->view)->with([
            'action'    => $this->action ?: $reset,
            'layout'    => $this->layout,
            'filterID'  => $this->filterID,
            'expand'    => $this->expand,
            'resetUrl'  => $this->getWithoutQueriesUrl()
        ])->render();
    }

    /**
     * Generate a filter object and add to grid.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return AbstractFilter|$this
     */
    public function __call($method, $arguments)
    {
        if (in_array($method, $this->supports)) {
            $className = '\\Swoft\\Admin\\Grid\\Filter\\'.ucfirst($method);

            return $this->addFilter(new $className(...$arguments));
        }

        return $this;
    }
}
