<?php

namespace Swoft\Admin\Grid\Traits;

/**
 * 禁用项接口
 */
trait DisableOptions
{
    /**
     * 禁用分页
     *
     * @return $this
     */
    public function disablePagination()
    {
        $this->model->usePaginate(false);

        $this->option('usePagination', false);

        return $this;
    }

    /**
     * 禁用视图按钮
     *
     * @return $this
     */
    public function disableView()
    {
        $this->options['useViewAction'] = false;
        return $this;
    }

    /**
     * 禁用编辑按钮
     *
     * @return $this
     */
    public function disableEdit()
    {
        $this->options['useEditAction'] = false;
        return $this;
    }

    /**
     * 禁用编辑按钮
     *
     * @return $this
     */
    public function disableDelete()
    {
        $this->options['useDeleteAction'] = false;
        return $this;
    }

    /**
     * 禁用批量删除
     *
     * @return $this
     */
    public function disableBatchDelete()
    {
        $this->tools->disableBatchDelete();
        return $this;
    }

    /**
     * 禁用刷新按钮
     *
     * @return $this
     */
    public function disableRefreshButton()
    {
        $this->tools->disableRefreshButton();
        return $this;
    }

    /**
     * 禁用操作列
     *
     * @return $this
     */
    public function disableActions()
    {
        return $this->option('useActions', false);
    }

    /**
     * 禁用行选择器
     *
     * @return $this
     */
    public function disableRowSelector()
    {
        $this->tools->disableBatchActions();

        return $this->option('useRowSelector', false);
    }

    /**
     * 禁用过滤器
     *
     * @return $this
     */
    public function disableFilter()
    {
        $this->option('useFilter', false);

        $this->tools->disableFilterButton();

        return $this;
    }

    /**
     * 禁用导出
     *
     * @return $this
     */
    public function disableExport()
    {
        return $this->option('useExporter', false);
    }

    /**
     * 禁用创建按钮
     *
     * @return $this
     *
     * @deprecated
     */
    public function disableCreation()
    {
        return $this->option('allowCreate', false);
    }
}
