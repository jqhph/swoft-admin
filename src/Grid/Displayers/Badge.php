<?php

namespace Swoft\Admin\Grid\Displayers;

use Swoft\Contract\Arrayable;

class Badge extends AbstractDisplayer
{
    /**
     * @var string
     */
    protected $background = 'red';

    /**
     * 设置样式
     *
     * @param string $style
     * @return $this
     */
    public function background(string $style)
    {
        $this->background = $style;
        return $this;
    }

    public function display(string $style = null)
    {
        if ($style) {
            $this->background($style);
        }

        if ($this->value instanceof Arrayable) {
            $this->value = $this->value->toArray();
        } elseif (is_string($this->value)) {
            $this->value = explode(',', $this->value);
        }

        return collect((array) $this->value)->map(function ($name) use ($style) {
            if ($name === '' || $name === null) {
                return '';
            }
            return "<span class='badge bg-{$this->background}'>$name</span>";
        })->implode('&nbsp;');
    }
}
