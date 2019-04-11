<?php

namespace Swoft\Admin\Grid\Exporters;

use Swoft\Admin\Admin;
use Swoft\Admin\Grid;
use Swoft\Support\Collection;
use Zend\Stdlib\ResponseInterface;

abstract class AbstractExporter implements ExporterInterface
{
    /**
     * @var Grid
     */
    protected $grid;

    /**
     * Create a new exporter instance.
     *
     * @param $grid
     */
    public function __construct(Grid $grid = null)
    {
        if ($grid) {
            $this->setGrid($grid);
        }
    }

    /**
     * Set grid for exporter.
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
     * @return string
     */
    protected function getFilename()
    {
        $controller = Admin::getControllerName();

        return $controller.'-'.date('YmdHis');
    }

    /**
     * Get data with export query.
     *
     * @return Collection
     */
    public function getData()
    {
        return $this->grid->getFilter()->execute(true);
    }

    /**
     * Export data with scope.
     *
     * @param string $scope
     *
     * @return $this
     */
    public function withScope($scope)
    {
        if ($scope == Grid\Exporter::SCOPE_ALL) {
            return $this;
        }

        list($scope, $args) = explode(':', $scope);

        if ($scope == Grid\Exporter::SCOPE_CURRENT_PAGE) {
            $this->grid->model()->usePaginate(true);
        }

        if ($scope == Grid\Exporter::SCOPE_SELECTED_ROWS) {
            $selected = explode(',', $args);
            $this->grid->model()->whereIn($this->grid->getKeyName(), $selected);
        }

        return $this;
    }

    /**
     * 执行导出操作
     *
     * @param \Closure|null $filter
     * @return ResponseInterface
     */
    abstract public function export(\Closure $filter = null);
}
