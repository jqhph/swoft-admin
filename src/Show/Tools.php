<?php

namespace Swoft\Admin\Show;

use Swoft\Admin\Admin;
use Swoft\Support\Contracts\Htmlable;
use Swoft\Support\Collection;
use Swoft\Support\Contracts\Renderable;

class Tools implements Renderable
{
    /**
     * The panel that holds this tool.
     *
     * @var Panel
     */
    protected $panel;

    /**
     * @var string
     */
    protected $resource;

    /**
     * Default tools.
     *
     * @var array
     */
    protected $tools = ['delete', 'edit', 'list'];

    /**
     * Tools should be appends to default tools.
     *
     * @var Collection
     */
    protected $appends;

    /**
     * Tools should be prepends to default tools.
     *
     * @var Collection
     */
    protected $prepends;

    /**
     * Tools constructor.
     *
     * @param Panel $panel
     */
    public function __construct(Panel $panel)
    {
        $this->panel = $panel;

        $this->appends = new Collection();
        $this->prepends = new Collection();
    }

    /**
     * Append a tools.
     *
     * @param mixed $tool
     *
     * @return $this
     */
    public function append($tool)
    {
        $this->appends->push($tool);

        return $this;
    }

    /**
     * Prepend a tool.
     *
     * @param mixed $tool
     *
     * @return $this
     */
    public function prepend($tool)
    {
        $this->prepends->push($tool);

        return $this;
    }

    /**
     * Disable `list` tool.
     *
     * @return $this
     */
    public function disableList()
    {
        array_delete($this->tools, 'list');

        return $this;
    }

    /**
     * Disable `delete` tool.
     *
     * @return $this
     */
    public function disableDelete()
    {
        array_delete($this->tools, 'delete');

        return $this;
    }

    /**
     * Disable `edit` tool.
     *
     * @return $this
     */
    public function disableEdit()
    {
        array_delete($this->tools, 'edit');

        return $this;
    }

    /**
     * Get request path for resource list.
     *
     * @return string
     */
    protected function getListPath()
    {
        return Admin::url()->list();
    }

    /**
     * Get request path for edit.
     *
     * @return string
     */
    protected function getEditPath()
    {
        $key = $this->panel->getParent()->getId();

        return Admin::url()->edit($key);
    }

    /**
     * Get request path for delete.
     *
     * @return string
     */
    protected function getDeletePath()
    {
        $key = $this->panel->getParent()->getId();

        return Admin::url()->delete($key);
    }

    /**
     * Render `list` tool.
     *
     * @return string
     */
    protected function renderList()
    {
        $list = t('List', 'admin');

        return <<<HTML
<div class="btn-group pull-right" style="margin-right: 5px">
    <a href="{$this->getListPath()}" class="btn btn-sm btn-default" title="{$list}">
        <i class="fa fa-list"></i><span class="hidden-xs"> {$list}</span>
    </a>
</div>
HTML;
    }

    /**
     * Render `edit` tool.
     *
     * @return string
     */
    protected function renderEdit()
    {
        $edit = t('Edit', 'admin');

        return <<<HTML
<div class="btn-group pull-right" style="margin-right: 5px">
    <a href="{$this->getEditPath()}" class="btn btn-sm btn-info" title="{$edit}">
        <i class="fa fa-edit"></i><span class="hidden-xs"> {$edit}</span>
    </a>
</div>
HTML;
    }

    /**
     * Render `delete` tool.
     *
     * @return string
     */
    protected function renderDelete()
    {
        $deleteConfirm = t('Are you sure to delete this item?', 'admin');
        $confirm = t('Confirm', 'admin');
        $cancel = t('Cancel', 'admin');

        $class = uniqid();

        $script = <<<SCRIPT
$('.{$class}-delete').unbind('click').click(function() {
    LA.confirm('$deleteConfirm', function () {
         $.ajax({
            method: 'delete',
            url: '{$this->getDeletePath()}',
            data: {
                _method:'delete',
                _token:LA.token
            },
            success: function (data) {
                 $.pjax({container:'#pjax-container', url: '{$this->getListPath()}' });

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

        $delete = t('Delete', 'admin');

        Admin::script($script);

        return <<<HTML
<div class="btn-group pull-right" style="margin-right: 5px">
    <a href="javascript:void(0);" class="btn btn-sm btn-danger {$class}-delete" title="{$delete}">
        <i class="fa fa-trash"></i><span class="hidden-xs">  {$delete}</span>
    </a>
</div>
HTML;
    }

    /**
     * Render custom tools.
     *
     * @param Collection $tools
     *
     * @return mixed
     */
    protected function renderCustomTools($tools)
    {
        return $tools->map(function ($tool) {
            if ($tool instanceof Renderable) {
                return $tool->render();
            }

            if ($tool instanceof Htmlable) {
                return $tool->toHtml();
            }

            return (string) $tool;
        })->implode(' ');
    }

    /**
     * Render tools.
     *
     * @return string
     */
    public function render()
    {
        $output = $this->renderCustomTools($this->prepends);

        foreach ($this->tools as $tool) {
            $renderMethod = 'render'.ucfirst($tool);
            $output .= $this->$renderMethod();
        }

        return $output.$this->renderCustomTools($this->appends);
    }
}
