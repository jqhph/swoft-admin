<?php

namespace Swoft\Admin\Grid\Displayers;

use Swoft\Admin\Admin;

class Actions extends AbstractDisplayer
{
    /**
     * @var array
     */
    protected $appends = [];

    /**
     * @var array
     */
    protected $prepends = [];

    /**
     * Default actions.
     *
     * @var array
     */
    protected $actions = ['view', 'edit', 'delete'];

    /**
     * @var string
     */
    protected $resource;

    /**
     * Append a action.
     *
     * @param $action
     *
     * @return $this
     */
    public function append($action)
    {
        array_push($this->appends, $action);

        return $this;
    }

    /**
     * Prepend a action.
     *
     * @param $action
     *
     * @return $this
     */
    public function prepend($action)
    {
        array_unshift($this->prepends, $action);

        return $this;
    }

    /**
     * Disable view action.
     *
     * @return $this
     */
    public function disableView()
    {
        array_delete($this->actions, 'view');

        return $this;
    }

    /**
     * Disable delete.
     *
     * @return $this.
     */
    public function disableDelete()
    {
        array_delete($this->actions, 'delete');

        return $this;
    }

    /**
     * Disable edit.
     *
     * @return $this.
     */
    public function disableEdit()
    {
        array_delete($this->actions, 'edit');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function display($callback = null)
    {
        if ($callback instanceof \Closure) {
            $callback->call($this, $this);
        }

        $actions = $this->prepends;

        foreach ($this->actions as $action) {
            $method = 'render'.ucfirst($action);
            array_push($actions, $this->{$method}());
        }

        $actions = array_merge($actions, $this->appends);

        return implode('', $actions);
    }

    /**
     * Render view action.
     *
     * @return string
     */
    protected function renderView()
    {
        $view = Admin::url()->view($this->getPrimaryKey());
        return <<<EOT
<a href="$view"><i class="fa fa-eye"></i></a>&nbsp;&nbsp;
EOT;
    }

    /**
     * Render edit action.
     *
     * @return string
     */
    protected function renderEdit()
    {
        $edit = Admin::url()->edit($this->getPrimaryKey());

        return <<<EOT
<a href="$edit"><i class="fa fa-edit"></i></a>&nbsp;
EOT;
    }

    /**
     * Render delete action.
     *
     * @return string
     */
    protected function renderDelete()
    {
        $deleteConfirm = t('Are you sure to delete this item?', 'admin');
        $confirm       = t('Confirm', 'admin');
        $cancel        = t('Cancel', 'admin');

        $deleteUrl = Admin::url()->delete();

        $script = <<<SCRIPT
$('.{$this->grid->getGridRowName()}-delete').unbind('click').click(function() {
    var id = $(this).data('id'), url = '{$deleteUrl}';
    LA.confirm('$deleteConfirm', function () {
        $.ajax({
            method: 'delete',
            url: url.replace(':id', id),
            data: {
                _method:'delete',
                _token:LA.token
            },
            success: function (data) {
                $.pjax.reload('#pjax-container');
                if (typeof data === 'object') {
                    if (data.status) {
                        LA.success(data.message);
                    } else {
                        LA.error(data.message);
                    }
                }
            }
        });
    }, '$confirm','$cancel');
});
SCRIPT;

        Admin::script($script);

        return <<<EOT
<a href="javascript:void(0);" data-id="{$this->getPrimaryKey()}" class="{$this->grid->getGridRowName()}-delete">
    <i class="fa fa-trash"></i>
</a>
EOT;
    }
}
