<?php

namespace Swoft\Admin\Grid\Filter\Layout;

use Swoft\Admin\Grid\Filter;
use Swoft\Support\Collection;

class Layout
{
    /**
     * @var Collection
     */
    protected $columns;

    /**
     * @var Column
     */
    protected $current;

    /**
     * @var Filter
     */
    protected $parent;

    /**
     * Layout constructor.
     *
     * @param Filter $filter
     */
    public function __construct(Filter $filter)
    {
        $this->parent = $filter;

        $this->current = new Column();

        $this->columns = new Collection();
    }

    /**
     * Add a filter to layout column.
     *
     * @param Filter\AbstractFilter $filter
     */
    public function addFilter(Filter\AbstractFilter $filter)
    {
        $this->current->addFilter($filter);
    }

    /**
     * Get all columns in filter layout.
     *
     * @return Collection
     */
    public function columns()
    {
        if ($this->columns->isEmpty()) {
            $this->columns->push($this->current);
        }

        return $this->columns;
    }
}
