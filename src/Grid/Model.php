<?php

namespace Swoft\Admin\Grid;

use Swoft\Admin\Admin;
use Swoft\Admin\Grid;
use Swoft\Admin\Widgets\Paginator;
use Swoft\Support\Arr;
use Swoft\Support\Collection;
use Swoft\Support\Str;
use Swoft\Support\Input;

class Model
{
    /**
     * Array of queries of the eloquent model.
     *
     * @var \Swoft\Support\Collection
     */
    protected $queries;

    /**
     * Sort parameters of the model.
     *
     * @var array
     */
    protected $sort;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var int
     */
    protected $total = 0;

    /*
     * 20 items per page as default.
     *
     * @var int
     */
    protected $perPage = 20;

    /**
     * 分页栏按钮显示数量
     *
     * @var int
     */
    protected $pageButtonNumber = 8;

    /**
     * If the model use pagination.
     *
     * @var bool
     */
    protected $usePaginate = true;

    /**
     * @var string
     */
    protected $pageName = 'page';

    /**
     * The query string variable used to store the per-page.
     *
     * @var string
     */
    protected $perPageName = 'per_page';

    /**
     * The query string variable used to store the sort.
     *
     * @var string
     */
    protected $sortName = '_sort';

    /**
     * Collection callback.
     *
     * @var \Closure
     */
    protected $collectionCallback;

    /**
     * @var Grid
     */
    protected $grid;

    /**
     * @var array
     */
    protected $eagerLoads = [];

    /**
     * Create a new grid model instance.
     */
    public function __construct(Grid $grid)
    {
        $this->setGrid($grid);
        $this->queries = collect();
    }

    /**
     * 获取主键名称
     *
     * @return string
     */
    public function getPrimaryKeyName()
    {
        return $this->grid->getKeyName();
    }

    /**
     * 设置主键键名
     *
     * @param string $name
     * @return $this
     */
    public function setKeyName(string $name)
    {
        $this->grid->setKeyName($name);
        return $this;
    }

    /**
     * 设置数据行
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = &$data;
        return $this;
    }

    /**
     * @param int $per
     * @return $this|int
     */
    public function paginate(int $per = null)
    {
        if ($per === null) {
            return $this->perPage;
        }
        $this->perPage = $per;
        return $this;
    }

    /**
     * 设置分页按钮显示数量
     * 
     * @param int $num
     * @return $this
     */
    public function setPageButtonNumber(int $num)
    {
        $this->pageButtonNumber = $num;

        return $this;
    }

    /**
     * 设置总记录数
     *
     * @param int $total
     * @return $this
     */
    public function setTotal(int $total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return Paginator
     */
    public function paginator()
    {
        $paginator = new Paginator(
            $this->total,
            $this->perPage,
            $this->pageButtonNumber
        );
        $paginator->setData($this->data);
        $paginator->setPageName($this->pageName);

        return $paginator;
    }

    /**
     * Enable or disable pagination.
     *
     * @param bool $use
     */
    public function usePaginate($use = true)
    {
        $this->usePaginate = $use;
    }

    /**
     * @return bool
     */
    public function allowPaginate()
    {
        return $this->usePaginate;
    }

    /**
     * Get the query string variable used to store the per-page.
     *
     * @return string
     */
    public function getPerPageName()
    {
        return $this->perPageName;
    }

    /**
     * Set the query string variable used to store the per-page.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setPerPageName($name)
    {
        $this->perPageName = $name;

        return $this;
    }

    /**
     * 分页key值
     *
     * @return string
     */
    public function getPageName()
    {
        return $this->pageName;
    }

    /**
     * Get the query string variable used to store the sort.
     *
     * @return string
     */
    public function getSortName()
    {
        return $this->sortName;
    }

    /**
     * Set the query string variable used to store the sort.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setSortName($name)
    {
        $this->sortName = $name;

        return $this;
    }

    /**
     * Set parent grid instance.
     *
     * @param Grid $grid
     *
     * @return $this
     */
    public function setGrid(Grid $grid)
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * Get parent gird instance.
     *
     * @return Grid
     */
    public function getGrid()
    {
        return $this->grid;
    }

    /**
     * Set collection callback.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function collection(\Closure $callback = null)
    {
        $this->collectionCallback = $callback;

        return $this;
    }

    /**
     * Build.
     *
     * @param bool $toArray
     *
     * @return array|Collection|mixed
     */
    public function buildData($toArray = true)
    {
        if (empty($this->data)) {
            $collection = $this->get();

            if ($this->collectionCallback) {
                $collection = call_user_func($this->collectionCallback, $collection);
            }

            if ($toArray) {
                $this->data = $collection->toArray();
            } else {
                $this->data = $collection;
            }
        }

        return $this->data;
    }

    /**
     * Add conditions to grid model.
     *
     * @param array $conditions
     *
     * @return $this
     */
    public function addConditions(array $conditions)
    {
        foreach ($conditions as $condition) {
            call_user_func_array([$this, key($condition)], current($condition));
        }

        $this->setSort();
        $this->setPaginate();

        admin_debug("Grid queries", $this->queries);

        return $this;
    }

    /**
     *
     * @return Collection
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * 获取分页参数
     *
     * @return array [$pageSize, $offset] 或 []
     */
    public function getPaginatorValues()
    {
        return $this->queries
            ->map(function ($value) {
                if ($value['method'] == 'limit') {
                    return $value['arguments'];
                }
            })
            ->filter()
            ->first() ?: [];
    }

    /**
     * 获取排序参数
     *
     * @return array ['id', 'desc'] 或 []
     */
    public function getOrderByValues()
    {
        return $this->queries
            ->map(function ($value) {
                if ($value['method'] == 'orderBy') {
                    return $value['arguments'];
                }
            })
            ->filter()
            ->first();
    }

    /**
     * 获取搜索条件
     *
     * @return Collection ['field' => ['value', '>='], ...]
     */
    public function getFilterConditionValues()
    {
        $q = new Collection();
        $this->queries
            ->each(function ($value) use(&$q) {
                if ($value['method'] != 'orderBy' && $value['method'] != 'limit') {
                    list($field, $val) = $value['arguments'];

                    $q[$field] = [$val, array_get($value['arguments'], 2, '=')];
                }
            });

        return $q;
    }

    /**
     * @return Collection
     */
    protected function get()
    {
        $result = Admin::repository()->find($this);

        $this->setData($result[0] ?? []);
        $this->setTotal($result[1] ?? 0);

        return new Collection($this->data);
    }

    /**
     * Set the grid paginate.
     *
     * @return void
     */
    protected function setPaginate()
    {
        $paginate = $this->findQueryByMethod('limit');
        if ($paginate) {
            return;
        }

        $this->queries = $this->queries->reject(function ($query) {
            return $query['method'] == 'limit';
        });

        if (!$this->usePaginate) {
            return;
        }
        $query = [
            'method'    => 'limit',
            'arguments' => $this->resolvePerPage(),
        ];

        $this->queries->push($query);

    }

    /**
     * Resolve perPage for pagination.
     *
     * @return array
     */
    protected function resolvePerPage()
    {
        $input = Input::make();

        $this->perPage = $perPage = (int)$input->get($this->perPageName) ?: $this->perPage;
        if ($perPage < 1) {
            $perPage = $this->perPage;
        }

        $page = (int)$input->get($this->pageName);
        if ($page <= 0) {
            $page = 1;
        }

        $offset = ($page - 1) * $perPage;

        return [$perPage, $offset];
    }

    /**
     * Find query by method name.
     *
     * @param $method
     *
     * @return static
     */
    protected function findQueryByMethod($method)
    {
        return $this->queries->first(function ($query) use ($method) {
            return $query['method'] == $method;
        });
    }

    /**
     * Set the grid sort.
     *
     * @return void
     */
    protected function setSort()
    {
        $this->sort = http_get($this->sortName, []);
        if (!$this->sort && $def = $this->grid->getAttribute('cloumn-default-order')) {
            list($column, $type) = explode(',', $def);
            $this->sort = [
                'column' => $column,
                'type'   => $type,
            ];
        }

        if (!is_array($this->sort)) {
            return;
        }

        if (empty($this->sort['column']) || empty($this->sort['type'])) {
            return;
        }

        $this->resetOrderBy();

        $this->queries->push([
            'method'    => 'orderBy',
            'arguments' => [$this->sort['column'], $this->sort['type']],
        ]);
    }


    /**
     * Reset orderBy query.
     *
     * @return void
     */
    public function resetOrderBy()
    {
        $this->queries = $this->queries->reject(function ($query) {
            return $query['method'] == 'orderBy';
        });
    }


    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return $this
     */
    public function __call($method, $arguments)
    {
        $this->queries->push([
            'method'    => $method,
            'arguments' => $arguments,
        ]);

        return $this;
    }

    /**
     * Set the relationships that should be eager loaded.
     *
     * @param mixed $relations
     *
     * @return $this|Model
     */
    public function with($relations)
    {
        if (is_array($relations)) {
            if (Arr::isAssoc($relations)) {
                $relations = array_keys($relations);
            }

            $this->eagerLoads = array_merge($this->eagerLoads, $relations);
        }

        if (is_string($relations)) {
            if (Str::contains($relations, '.')) {
                $relations = explode('.', $relations)[0];
            }

            if (Str::contains($relations, ':')) {
                $relations = explode(':', $relations)[0];
            }

            if (in_array($relations, $this->eagerLoads)) {
                return $this;
            }

            $this->eagerLoads[] = $relations;
        }

        return $this->__call('with', (array) $relations);
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        $data = $this->buildData();

        if (array_key_exists($key, $data)) {
            return $data[$key];
        }
    }
}
