<?php

namespace Swoft\Admin\Grid\Filter\Layout;

use Swoft\Admin\Grid\Filter\AbstractFilter;
use Swoft\Support\Collection;

class Column
{
    /**
     * @var Collection
     */
    protected $filters;

    /**
     * Column constructor.
     */
    public function __construct()
    {
        $this->filters = new Collection();
    }


    /**
     * Set column width.
     *
     * @param int $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * Get column width.
     *
     * @return int
     */
    public function width()
    {
        return $this->width;
    }


    /**
     * Add a filter to this column.
     *
     * @param AbstractFilter $filter
     */
    public function addFilter(AbstractFilter $filter)
    {
        $this->filters->push($filter);
    }

    /**
     * Get all filters in this column.
     *
     * @return Collection
     */
    public function filters()
    {
        return $this->filters;
    }
}
