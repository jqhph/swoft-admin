<?php

namespace Swoft\Admin\Grid\Displayers;

class Button extends AbstractDisplayer
{
    /**
     * @var string
     */
    protected $style = 'success';

    /**
     * 设置样式
     *
     * @param string $style
     * @return $this
     */
    public function style(string $style)
    {
        $this->style = $style;
        return $this;
    }

    public function display(string $style = null)
    {
        if ($style) {
            $this->style($style);
        }

        $style = collect((array) $style)->map(function ($style) {
            return 'btn-'.$style;
        })->implode(' ');

        return "<span class='btn $style'>{$this->value}</span>";
    }
}
