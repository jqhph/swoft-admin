<?php

namespace Swoft\Admin\Grid;

use Swoft\Admin\Grid;
use Swoft\Admin\Grid\Tools\AbstractTool;
use Swoft\Admin\Grid\Tools\BatchActions;
use Swoft\Admin\Grid\Tools\FilterButton;
use Swoft\Admin\Grid\Tools\RefreshButton;
use Swoft\Support\Collection;
use Swoft\Support\Contracts\Renderable;

class Tools implements Renderable
{
    /**
     * Parent grid.
     *
     * @var Grid
     */
    protected $grid;

    /**
     * Collection of tools.
     *
     * @var Collection
     */
    protected $tools;

    /**
     * Create a new Tools instance.
     *
     * @param Grid $grid
     */
    public function __construct(Grid $grid)
    {
        $this->grid = $grid;

        $this->tools = new Collection();

        $this->appendDefaultTools();
    }

    /**
     * Append default tools.
     */
    protected function appendDefaultTools()
    {
        $this->append(new BatchActions())
            ->append(new RefreshButton())
            ->append(new FilterButton());
    }

    /**
     * Append tools.
     *
     * @param AbstractTool|string $tool
     *
     * @return $this
     */
    public function append($tool)
    {
        $this->tools->push($tool);

        return $this;
    }

    /**
     * Prepend a tool.
     *
     * @param AbstractTool|string $tool
     *
     * @return $this
     */
    public function prepend($tool)
    {
        $this->tools->prepend($tool);

        return $this;
    }

    /**
     * Disable filter button.
     *
     * @return void
     */
    public function disableFilterButton()
    {
        $this->tools = $this->tools->reject(function ($tool) {
            return $tool instanceof FilterButton;
        });
    }

    /**
     * Disable refresh button.
     *
     * @return void
     */
    public function disableRefreshButton()
    {
        $this->tools = $this->tools->reject(function ($tool) {
            return $tool instanceof RefreshButton;
        });
    }

    /**
     * Disable batch actions.
     *
     * @return void
     */
    public function disableBatchActions()
    {
        $this->tools = $this->tools->reject(function ($tool) {
            return $tool instanceof BatchActions;
        });
    }

    /**
     * 禁用批量删除
     *
     * @return void
     */
    public function disableBatchDelete()
    {
        $tool = $this->tools->first(function ($tool) {
            return $tool instanceof BatchActions;
        });

        $tool ? $tool->disableDelete() : null;
    }

    /**
     * 批量操作
     *
     * @param \Closure $closure
     */
    public function batch(\Closure $closure)
    {
        call_user_func($closure, $this->tools->first(function ($tool) {
            return $tool instanceof BatchActions;
        }));
    }

    /**
     * Render header tools bar.
     *
     * @return string
     */
    public function render()
    {
        return $this->tools->map(function ($tool) {
            if ($tool instanceof AbstractTool) {
                return $tool->setGrid($this->grid)->render();
            }

            return (string) $tool;
        })->implode(' ');
    }
}
