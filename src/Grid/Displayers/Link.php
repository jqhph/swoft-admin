<?php

namespace Swoft\Admin\Grid\Displayers;

class Link extends AbstractDisplayer
{
    public function display($href = '', $target = '_blank')
    {
        $href = $href ?: $this->value;
        if (!$href) {
            return '';
        }
        return "<a href='$href' target='$target'>{$this->value}</a>";
    }
}
