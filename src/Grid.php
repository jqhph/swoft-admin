<?php

namespace Swoft\Admin;

use Closure;
use Swoft\Admin\Grid\AbstractGrid;
use Swoft\Admin\Grid\Column;
use Swoft\Admin\Grid\Exporters\ExporterInterface;
use Swoft\Admin\Grid\Traits\DisableOptions;
use Swoft\Admin\Grid\Exporter;
use Swoft\Admin\Grid\Filter;
use Swoft\Admin\Grid\Header;
use Swoft\Admin\Grid\Model;
use Swoft\Admin\Grid\Responsive;
use Swoft\Admin\Grid\Tools;
use Swoft\Support\Collection;

class Grid extends AbstractGrid
{
    use DisableOptions;

    /**
     * 导出
     *
     * @param Closure|null $filter
     * @return null|\Psr\Http\Message\ResponseInterface
     */
    public function export(\Closure $filter = null)
    {
        if (!$scope = $this->request->query(Exporter::$queryName)) {
           return null;
        }
        $this->model()->usePaginate(false);

        if ($this->builder) {
            call_user_func($this->builder, $this);
        }

        return (new Exporter($this))
            ->resolve($this->exporter)
            ->withScope($scope)
            ->export($filter);
    }

    /**
     * 获取主键名称
     *
     * @return string
     */
    public function getKeyName()
    {
        try {
            return Admin::repository()->getKeyName();
        } catch (\UnexpectedValueException $e) {
            return 'id';
        }
    }

    /**
     * 添加字段
     *
     * @param string $name
     * @param string $label
     *
     * @return Column|Collection
     */
    public function column($name, $label = '')
    {
        return $this->addColumn($name, $label);
    }

    /**
     * Get Grid model.
     *
     * @return Model
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * 设置每页显示记录数
     *
     * @param int $perPage
     * @return void
     */
    public function paginate(int $perPage)
    {
        $this->model->paginate($perPage);
    }

    /**
     * 设置分页按钮显示数量
     *
     * @param int $num
     * @return $this
     */
    public function setPageButtonNumber(int $num)
    {
        $this->model->setPageButtonNumber($num);
    }

    /**
     * If this grid use pagination.
     *
     * @return bool
     */
    public function allowPagination()
    {
        return $this->option('usePagination');
    }

    /**
     * Set per-page options.
     *
     * @param array $perPages
     * @return $this
     */
    public function perPages(array $perPages)
    {
        $this->perPages = $perPages;
        return $this;
    }

    /**
     * Set grid action callback.
     *
     * @param Closure $callback
     *
     * @return $this
     */
    public function actions(Closure $callback)
    {
        $this->actionsCallback = $callback;

        return $this;
    }

    /**
     * 设置grid外层包装容器
     *
     * @param Closure $closure
     * @return $this;
     */
    public function wrapper(\Closure $closure)
    {
        $this->wrapper = $closure;

        return $this;
    }

    /**
     * 获取过滤器对象
     *
     * @return Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * 设置过滤器
     *
     * @param Closure $callback
     */
    public function filter(Closure $callback)
    {
        call_user_func($callback, $this->filter);
    }

    /**
     * 显示搜索表单
     *
     * @return $this
     */
    public function expandFilter()
    {
        $this->filter->expand();

        return $this;
    }

    /**
     * 表格行内容回调方法设置
     *
     * @param Closure $callable
     *
     * @return Collection|null
     */
    public function rows(Closure $callable = null)
    {
        if (is_null($callable)) {
            return $this->rows;
        }

        $this->rowsCallback = $callable;
    }

    /**
     * 设置网格头工具
     *
     * @param Closure $callback
     * @return void
     */
    public function tools(Closure $callback)
    {
        call_user_func($callback, $this->tools);
    }

    /**
     * 批量操作设置
     *
     * @param Closure $callback $callback(Tools\BatchActions $actions)
     * @return $this
     */
    public function batch(Closure $callback)
    {
        $this->tools(function (Grid\Tools $tools) use ($callback) {
            $tools->batch($callback);
        });
        return $this;
    }

    /**
     * 设置导出处理对象
     *
     * @param ExporterInterface $exporter
     * @return $this
     */
    public function exporter(ExporterInterface $exporter)
    {
        $this->exporter = $exporter;

        return $this;
    }

    /**
     * 是否允许导出
     *
     * @return bool
     */
    public function allowExport()
    {
        return $this->option('useExporter');
    }

    /**
     * 启用 Responsive Tables 插件
     * @see https://github.com/nadangergeo/RWD-Table-Patterns
     *
     * @param array $opt
     * @return Responsive
     */
    public function responsive(array $opt = [])
    {
        $this->responsive = new Responsive($this);

        $this->responsive->option($opt);

        return $this->responsive;
    }

    /**
     * 一级表头设置
     * 调用此方法后会自动开启双表头模式
     *
     * @param string $label
     * @param array $columnNames
     * @return Header
     */
    public function header(string $label, array $columnNames)
    {
        if (!$columnNames || count($columnNames) < 2) {
            throw new \InvalidArgumentException('一级表头最少需要两个字段');
        }
        $this->bordered();

        return $this->headers[$label] = new Header($this, $label, $columnNames);
    }

    /**
     * 表格显示边框
     *
     * @return $this
     */
    public function bordered()
    {
        $this->options['useBordered'] = true;

        return $this;
    }

    /**
     * 是否允许显示创建按钮
     *
     * @return bool
     */
    public function allowCreation()
    {
        return $this->option('allowCreate');
    }

    /**
     * Set grid footer.
     *
     * @param Closure|null $closure
     * @return $this|Tools\Footer
     */
    public function footer(Closure $closure = null)
    {
        if (!$closure) {
            return $this->footer;
        }

        $this->footer = $closure;

        return $this;
    }

    /**
     * Add variables to grid view.
     *
     * @param array $variables
     *
     * @return $this
     */
    public function with($variables = [])
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * Set a view to render.
     *
     * @param string $view
     * @param array  $variables
     */
    public function setView(string $view, $variables = [])
    {
        if (!empty($variables)) {
            $this->with($variables);
        }

        $this->view = $view;
    }

    /**
     * 添加字段
     *
     * @param string $name
     * @return Column|Collection
     */
    public function __get($name)
    {
        return $this->addColumn($name);
    }

}
