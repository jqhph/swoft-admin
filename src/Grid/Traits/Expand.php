<?php

namespace Swoft\Admin\Grid\Traits;

use Swoft\Support\Contracts\Renderable;

trait Expand
{
    /**
     * 设置扩展行内容
     *
     * @param string|Renderable|\Closure $content
     * @return $this
     */
    public function addExpandRow($expand)
    {
        $rows = $this->getAttribute('__expand') ?: [];
        $rows[] = $expand;

        return $this->setAttribute('__expand', $rows);
    }

    /**
     * @return bool
     */
    public function hasExpands()
    {
        return $this->hasAttribute('__expand');
    }

    /**
     * 渲染扩展行
     *
     * @return string
     */
    public function renderExpands()
    {
        $expands = '';

        $col = count($this->columnNames);

        foreach ($this->pullExpandRows() as &$row) {
            if ($row instanceof \Closure) {
                $row = $row($this);
            }
            if ($row instanceof Renderable) {
                $row = $row->render();
            }
            $expands .= "<tr><td colspan='{$col}' style='padding:0;border:0;'>{$row}</td></tr>";
        }
        return $expands;
    }

    /**
     *
     * @return array
     */
    protected function pullExpandRows()
    {
        return (array)$this->pullAttribute('__expand');
    }

}
