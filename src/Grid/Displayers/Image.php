<?php

namespace Swoft\Admin\Grid\Displayers;

use Swoft\Contract\Arrayable;

class Image extends AbstractDisplayer
{
    public function display($server = '', int $width = 200, int $height = 200)
    {
        if ($this->value instanceof Arrayable) {
            $this->value = $this->value->toArray();
        }

        return collect((array) $this->value)->filter()->map(function ($path) use ($server, $width, $height) {
            $src = $path;
            if (is_valid_url($path)) {
                $src = $path;
            } elseif ($server) {
                $src = rtrim($server, '/').'/'.ltrim($path, '/');
            }

            return "<img src='$src' style='max-width:{$width}px;max-height:{$height}px' class='img img-thumbnail' />";
        })->implode('&nbsp;');
    }
}
