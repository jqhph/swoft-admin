<?php

namespace Swoft\Admin\Grid\Displayers;

class ProgressBar extends AbstractDisplayer
{
    /**
     * @var string
     */
    protected $style = 'primary';

    /**
     * @var string
     */
    protected $size = 'sm';

    /**
     * @var int
     */
    protected $max = 100;

    public function style(string $style)
    {
        $this->style = $style;

        return $this;
    }

    public function size(string $size)
    {
        $this->size = $size;

        return $this;
    }

    public function max(int $max)
    {
        $this->max = $max;

        return $this;
    }

    public function display(string $style = null, string $size = null, int $max = null)
    {
        $style = $style ?: $this->style;
        $size = $size ?: $this->size;
        $max = $max ?: $this->max;

        $style = collect((array) $style)->map(function ($style) {
            return 'progress-bar-'.$style;
        })->implode(' ');

        return <<<EOT
<div class="progress progress-$size">
    <div class="progress-bar $style" role="progressbar" aria-valuenow="{$this->value}" aria-valuemin="0" aria-valuemax="$max" style="width: {$this->value}%">
      <span class="sr-only">{$this->value}</span>
    </div>
</div>
EOT;
    }
}
