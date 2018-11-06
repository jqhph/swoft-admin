<?php

namespace Swoft\Admin\Grid\Displayers;

use Swoft\Contract\Arrayable;

class Label extends AbstractDisplayer
{
    public function display(string $style = 'success')
    {
        if ($this->value instanceof Arrayable) {
            $this->value = $this->value->toArray();
        } elseif (is_string($this->value)) {
            $this->value = explode(',', $this->value);
        }

        return collect((array) $this->value)->map(function ($name) use ($style) {
            if ($name === '' || $name === null) {
                return '';
            }

            return "<span class='label label-{$style}'>$name</span>";
        })->implode('&nbsp;');
    }
}
