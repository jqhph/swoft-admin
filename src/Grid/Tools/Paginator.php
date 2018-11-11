<?php

namespace Swoft\Admin\Grid\Tools;

use Swoft\Admin\Grid;
use Swoft\Admin\Widgets\Paginator as LengthAwarePaginator;
use Swoft\Core\RequestContext;

class Paginator extends AbstractTool
{
    /**
     * @var LengthAwarePaginator
     */
    protected $paginator = null;

    /**
     * Create a new Paginator instance.
     *
     * @param Grid $grid
     */
    public function __construct(Grid $grid)
    {
        $this->grid = $grid;

        $this->initPaginator();
    }

    /**
     * Initialize work for Paginator.
     *
     * @return void
     */
    protected function initPaginator()
    {
        $this->paginator = $this->grid->model()->paginator();
    }

    /**
     * Get Pagination links.
     *
     * @return string
     */
    protected function paginationLinks()
    {
        return blade('admin::pagination', ['pagination' => $this->paginator->render()])->render();
    }

    /**
     * Get per-page selector.
     *
     * @return PerPageSelector
     */
    protected function perPageSelector()
    {
        return new PerPageSelector($this->grid);
    }

    /**
     * Get range infomation of paginator.
     *
     * @return string
     */
    protected function paginationRanger()
    {
        $currentPage = $this->paginator->currentPage();
        $perPage = $this->paginator->getPerPage();

        $offset = ($currentPage - 1) * $perPage;
        $last = $offset + $perPage;
        $total = $this->paginator->total();

        $parameters = [
            'first' => $offset + 1,
            'last'  => $last > $total ? $total : $last,
            'total' => $total,
        ];

        $parameters = collect($parameters)->flatMap(function ($parameter, $key) {
            return [$key => "<b>$parameter</b>"];
        });

        return t('Showing :first to :last of :total entries', 'admin', $parameters->toArray());
    }

    /**
     * Render Paginator.
     *
     * @return string
     */
    public function render()
    {
        if (!$this->grid->allowPagination()) {
            return '';
        }

        return $this->paginationRanger().
            $this->paginationLinks().
            $this->perPageSelector();
    }
}
