<?php

namespace Swoft\Admin\Grid;

use Closure;
use Psr\Http\Message\RequestInterface;
use Swoft\Admin\Admin;
use Swoft\Admin\Exception\Handler;
use Swoft\Admin\Grid\Traits\Assets;
use Swoft\Admin\Grid\Traits\Attribute;
use Swoft\Admin\Grid\Traits\Expand;
use Swoft\Admin\Grid\Traits\HasElementNames;
use Swoft\Core\RequestContext;
use Swoft\Db\Model as Entity;
use Swoft\Support\Collection;
use Swoft\Support\Contracts\Renderable;
use Swoft\Support\Url;

abstract class AbstractGrid
{
    use HasElementNames,
        Attribute,
        Assets,
        Expand;

    /**
     * The grid data model instance.
     *
     * @var Model
     */
    protected $model;

    /**
     * Collection of all grid columns.
     *
     * @var Collection
     */
    protected $columns;

    /**
     * Collection of all data rows.
     *
     * @var \Swoft\Support\Collection
     */
    protected $rows;

    /**
     * Rows callable fucntion.
     *
     * @var \Closure
     */
    protected $rowsCallback;

    /**
     * All column names of the grid.
     *
     * @var array
     */
    public $columnNames = [];

    /**
     * Grid builder.
     *
     * @var \Closure
     */
    protected $builder;

    /**
     * Mark if the grid is builded.
     *
     * @var bool
     */
    protected $builded = false;

    /**
     * All variables in grid view.
     *
     * @var array
     */
    protected $variables = [];

    /**
     * The grid Filter.
     *
     * @var Filter
     */
    protected $filter;

    /**
     * Export driver.
     *
     * @var string
     */
    protected $exporter;

    /**
     * View for grid to render.
     *
     * @var string
     */
    protected $view = 'admin::grid.table';

    /**
     * Per-page options.
     *
     * @var array
     */
    public $perPages = [10, 20, 30, 50, 100, 200];

    /**
     * Header tools.
     *
     * @var Tools
     */
    protected $tools;

    /**
     * Callback for grid actions.
     *
     * @var Closure
     */
    protected $actionsCallback;

    /**
     * @var Header[]
     */
    protected $headers = [];

    /**
     * @var Closure
     */
    protected $wrapper;

    /**
     * @var Tree
     */
    protected $tree;

    /**
     * Options for grid.
     *
     * @var array
     */
    protected $options = [
        'usePagination'   => true,
        'useFilter'       => true,
        'useExporter'     => true,
        'useActions'      => true,
        'useRowSelector'  => true,
        'allowCreate'     => true,
        'useViewAction'   => true,
        'useEditAction'   => true,
        'useDeleteAction' => true,
        'useTree'         => false,
        'useBordered'     => false,
    ];

    /**
     * @var Responsive
     */
    protected $responsive;

    /**
     * @var Tools\Footer
     */
    protected $footer;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var string
     */
    public $rowSelectorTextColumn;

    /**
     * Create a new grid instance.
     *
     * @param Entity|callable $model
     * @param Closure  $builder
     */
    public function __construct(Closure $builder = null)
    {
        $this->model = new Model($this);
        $this->columns = new Collection();
        $this->rows = new Collection();
        $this->builder = $builder;
        $this->request = RequestContext::getRequest();
        $this->tools = new Tools($this);
        $this->filter = new Filter($this->model);
    }

    /**
     * 设置或获取配置项
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this|mixed
     */
    public function option($key, $value = null)
    {
        if (is_null($value)) {
            return $this->options[$key] ?? null;
        }

        $this->options[$key] = $value;

        return $this;
    }

    /**
     * 添加表格字段
     *
     * @param string $name
     * @param string $label
     *
     * @return Column
     */
    protected function addColumn($name = '', $label = '')
    {
        $column = new Column($name, $label);
        $column->setGrid($this);

        $this->columns->put($name, $column);
        return $column;
    }

    /**
     * 执行过滤查询操作
     *
     * @return array
     */
    public function processFilter()
    {
        if ($this->builder) {
            call_user_func($this->builder, $this);
        }

        $data = $this->filter->execute();

        if (!$data || !$this->options['useTree']) return $data;

        return Tree::make($this->getKeyName(), $data);
    }

    /**
     * 获取导出链接
     *
     * @param int  $scope
     * @param null $args
     * @return string
     */
    public function getExportUrl($scope = 1, $args = null)
    {
        $query = Url::query()->add(Exporter::formatExportQuery($scope, $args));

        return $query->build();
    }

    /**
     * Get the grid paginator.
     *
     * @return mixed
     */
    public function paginator()
    {
        return (new Tools\Paginator($this))->render();
    }

    /**
     * @return string
     */
    public function renderExportButton()
    {
        return (new Tools\ExportButton($this))->render();
    }

    /**
     * 获取大表头数组
     *
     * @return Header[]
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return Tools\Footer|string
     */
    public function renderFooter()
    {
        if (!$this->footer) {
            return '';
        }

        return new Tools\Footer($this);
    }

    /**
     * @return array
     */
    protected function variables()
    {
        $this->variables['grid'] = $this;

        return $this->variables;
    }

    /**
     * @return string
     */
    public function renderCreateButton()
    {
        return (new Tools\CreateButton($this))->render();
    }


    /**
     * 构建网格
     *
     * @return void
     */
    protected function build()
    {
        if ($this->builded) {
            return;
        }

        $data = $this->processFilter();

        if ($data) {
            $this->prependRowSelectorColumn();
        }
        $this->appendActionsColumn();

        Column::setOriginalGridData($data);

        $this->columns->map(function (Column $column) use (&$data) {
            $data = $column->fill($data);

            $this->columnNames[] = $column->getName();
        });

        $this->buildRows($data);

        $this->builded = true;

        if ($data && $this->responsive) {
            $this->responsive->build();
        }
        if ($this->headers) {
            $this->resortHeader();
        }
    }

    /**
     * @return string
     */
    public function renderHeaderTools()
    {
        return $this->tools->render();
    }

    /**
     * 增加 操作 列
     *
     * @return void
     */
    protected function appendActionsColumn()
    {
        if (!$this->option('useActions')) {
            return;
        }

        $grid = $this;
        $callback = $this->actionsCallback;
        $column = $this->addColumn('__actions__', t('Action', 'admin'));

        $column->display(function ($value) use ($grid, $column, $callback) {
            $actions = new Displayers\Actions($value, $grid, $column, $this);

            if (!$grid->options['useViewAction']) {
                $actions->disableView();
            }
            if (!$grid->options['useEditAction']) {
                $actions->disableEdit();
            }
            if (!$grid->options['useDeleteAction']) {
                $actions->disableDelete();
            }

            return $actions->display($callback);
        });
    }

    /**
     * @return Collection
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * 构建网格行内容
     *
     * @param array $data
     * @return void
     */
    protected function buildRows(array &$data)
    {
        $this->rows = collect($data)->map(function ($model, $number) {
            return new Row($number, $model);
        });

        if ($this->rowsCallback) {
            $this->rows->map($this->rowsCallback);
        }
    }

    /**
     * 重新分配表头
     */
    protected function resortHeader()
    {
        $originalHeaders = $this->headers;
        $originalColumns = $this->columns;

        $headersColumns = $this->headers = $this->columns = [];

        // 获取已知一级表头字段
        foreach ($originalHeaders as $header) {
            $headersColumns = array_merge(
                $headersColumns,
                $tmp = $header->getColumnNames()
            );
            foreach ($tmp as &$name) {
                if ($column = $originalColumns->get($name)) {
                    $this->columns[$name] = $column;
                }
            }
        }

        // 排序
        $before = $after = [];
        $isBefore = true;
        foreach ($originalColumns as $name => $column) {
            if ($isBefore && !isset($this->columns[$name])) {
                $before[$name] = $column;
                continue;
            }
            $isBefore = false;
            if (!isset($this->columns[$name])) {
                $after[$name] = $column;
            }
        }

        // 合并前/后部分表头
        $beforeHeaders = $this->createHeaderWithColumns($before);
        $afterHeaders  = $this->createHeaderWithColumns($after);

        // 对字段进行重新排序
        $this->columnNames = array_merge(
            array_keys($before),
            array_keys($this->columns),
            array_keys($after)
        );

        $this->columns = new Collection(array_values($this->columns));
        $this->headers = array_merge(
            $beforeHeaders,
            array_values($originalHeaders),
            $afterHeaders
        );
    }

    protected function createHeaderWithColumns(array $columns)
    {
        $headers = [];
        /* @var Column $column */
        foreach ($columns as $name => $column) {
            $header = new Header($this, $column->getLabel(), [$name]);
            $prio = $column->getDataPriority();
            if (is_int($prio)) {
                $header->responsive($prio);
            }
            if ($sorter = $column->sorter()) {
                $header->setSorter($sorter);
            }
            $headers[] = $header;
        }
        return $headers;
    }


    /**
     * 行选择器
     *
     * @return void
     */
    protected function prependRowSelectorColumn()
    {
        if (!$this->option('useRowSelector')) {
            return;
        }

        $grid = $this;

        $column = new Column(
            Column::SELECT_COLUMN_NAME,
            "<input type=\"checkbox\" class=\"{$this->getSelectAllName()}\" />&nbsp;"
        );
        $column->setGrid($this);

        $column->display(function ($value) use ($grid, $column) {
            $actions = new Displayers\RowSelector($value, $grid, $column, $this);

            return $actions->display();
        });

        $this->columns->prepend($column, Column::SELECT_COLUMN_NAME);

        Admin::script(
            <<<EOT
$('.{$this->getSelectAllName()}').iCheck({checkboxClass:'icheckbox_minimal-blue'});
$('.{$this->getSelectAllName()}').on('ifChanged', function(event) {
    if (this.checked) {
        $('.{$this->getGridRowName()}-checkbox').iCheck('check');
    } else {
        $('.{$this->getGridRowName()}-checkbox').iCheck('uncheck');
    }
});
EOT
        );
    }

    /**
     * 渲染过滤器
     *
     * @return string
     */
    public function renderFilter()
    {
        if (!$this->option('useFilter')) {
            return '';
        }

        return $this->filter->render();
    }

    /**
     * 构建树状结构行
     *
     * @param int $level
     * @param array $children
     * @return $this
     */
    public function buildTree(int $level, array $children)
    {
        Column::setOriginalGridData($children);

        $this->columns->map(function (Column $column) use (&$children) {
            $children = $column->fill($children);
        });
        $children = collect($children)->map(function ($model, $number) {
            return new Row($number, $model);
        });

        $this->tree = new Tree($level, $children, $this);
        return $this;
    }

    /**
     * @return Tree
     */
    public function pullTree()
    {
        $tree = $this->tree;
        $this->tree = null;
        return $tree;
    }

    /**
     * Get the string contents of the grid view.
     *
     * @return string
     * @throws \Exception
     */
    public function render()
    {
        try {
            $this->build();
            $this->collectAssets();
        } catch (\Exception $e) {
            if (Admin::isDebug()) {
                return Handler::renderException($e);
            }
            throw $e;
        }

        return $this->wrap();
    }

    /**
     * @return string
     */
    protected function wrap()
    {
        $view = blade($this->view, $this->variables());

        if (!$wrapper = $this->wrapper) {
            return "<div class='card'>{$view->render()}</div>";
        }

        $view = $wrapper($view);

        return $view instanceof Renderable ? $view->render() : $view;
    }

}
