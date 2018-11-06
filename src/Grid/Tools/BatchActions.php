<?php

namespace Swoft\Admin\Grid\Tools;

use Swoft\Admin\Admin;
use Swoft\Support\Collection;

class BatchActions extends AbstractTool
{
    /**
     * @var Collection
     */
    protected $actions;

    /**
     * @var bool
     */
    protected $enableDelete = true;

    /**
     * BatchActions constructor.
     */
    public function __construct()
    {
        $this->actions = new Collection();

        $this->appendDefaultAction();
    }

    /**
     * Append default action(batch delete action).
     *
     * return void
     */
    protected function appendDefaultAction()
    {
        $this->add(new BatchDelete(t('Delete', 'admin')));
    }

    /**
     * Disable delete.
     *
     * @return $this
     */
    public function disableDelete()
    {
        $this->enableDelete = false;

        return $this;
    }

    /**
     * Add a batch action.
     *
     * @param $title
     * @param BatchAction|null $action
     *
     * @return $this
     */
    public function add($title, BatchAction $action = null)
    {
        $id = $this->actions->count();

        if (func_num_args() == 1) {
            $action = $title;
            $action->setId($id);
        } elseif (func_num_args() == 2) {
            $action->setId($id);
            $action->setTitle($title);
        }

        $this->actions->push($action);

        return $this;
    }

    /**
     * Setup scripts of batch actions.
     *
     * @return void
     */
    protected function setUpScripts()
    {
        Admin::script($this->script());

        foreach ($this->actions as $action) {
            $action->setGrid($this->grid);

            Admin::script($action->script());
        }
    }

    /**
     * Scripts of BatchActions button groups.
     *
     * @return string
     */
    protected function script()
    {
        return '';
    }

    /**
     * Render BatchActions button groups.
     *
     * @return string
     */
    public function render()
    {
        if (!$this->enableDelete) {
            $this->actions = $this->actions->filter(function ($action) {
                return !$action instanceof BatchDelete;
            });
        }

        if ($this->actions->isEmpty()) {
            return '';
        }

        $this->setUpScripts();

        $data = [
            'actions'       => $this->actions,
        ];

        return blade('admin::grid.batch-actions', $data)->render();
    }
}
