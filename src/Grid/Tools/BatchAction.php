<?php

namespace Swoft\Admin\Grid\Tools;

use Swoft\Admin\Grid;
use Swoft\Support\SessionHelper;

abstract class BatchAction
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $resource;

    /**
     * @var Grid
     */
    protected $grid;

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param Grid $grid
     */
    public function setGrid(Grid $grid)
    {
        $this->grid = $grid;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        $session = SessionHelper::wrap();

        return $session ? $session->token() : '';
    }

    /**
     * @param bool $dotPrefix
     *
     * @return string
     */
    public function getElementClass($dotPrefix = true)
    {
        return sprintf(
            '%s%s-%s',
            $dotPrefix ? '.' : '',
            $this->grid->getGridBatchName(),
            $this->id
        );
    }

    /**
     * @return mixed
     */
    abstract public function script();
}
