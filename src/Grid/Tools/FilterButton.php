<?php

namespace Swoft\Admin\Grid\Tools;

use Swoft\Admin\Admin;

class FilterButton extends AbstractTool
{
    /**
     * @var string
     */
    protected $view = 'admin::filter.button';

    /**
     * @var string
     */
    protected $btnClassName;

    /**
     * @return \Swoft\Admin\Grid\Filter
     */
    protected function filter()
    {
        return $this->grid->getFilter();
    }

    /**
     * Get button class name.
     *
     * @return string
     */
    protected function getElementClassName()
    {
        if (!$this->btnClassName) {
            $this->btnClassName = uniqid().'-filter-btn';
        }

        return $this->btnClassName;
    }

    /**
     * Set up script for export button.
     */
    protected function setUpScripts()
    {
        $id = $this->filter()->getFilterID();

        Admin::script(<<<EOF
    $('.{$this->getElementClassName()}').click(function(){
        $('#{$id}').parent().collapse('toggle');
    }); 
EOF
);
    }

    /**
     * @return mixed
     */
    protected function renderScopes()
    {
        return $this->filter()->getScopes()->map->render()->implode("\r\n");
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $this->setUpScripts();

        $filter = $this->filter();
// ->filter()->getScopeCurrentLabel($key)
        $variables = [
            'filter'        => $filter,
            'scopes'        => $filter->getScopes(),
            'btn_class'     => $this->getElementClassName(),
            'expand'        => $filter->expand,
            'test' => 'testttt',
        ];

        return blade($this->view, $variables)->render();
    }
}
