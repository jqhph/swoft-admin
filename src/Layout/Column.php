<?php

namespace Swoft\Admin\Layout;

use Swoft\Admin\Grid;
use Swoft\Support\Contracts\Renderable;

class Column implements Buildable
{
    /**
     * @var int
     */
    protected $width = 12;

    /**
     * @var array
     */
    protected $contents = [];

    /**
     * Column constructor.
     *
     * @param $content
     * @param int $width
     */
    public function __construct($content, int $width = 12)
    {
        if ($content instanceof \Closure) {
            call_user_func($content, $this);
        } else {
            $this->append($content);
        }

        $this->width = $width;
    }

    /**
     * Append content to column.
     *
     * @param $content
     *
     * @return $this
     */
    public function append($content)
    {
        $this->contents[] = &$content;

        return $this;
    }

    /**
     * Add a row for column.
     *
     * @param $content
     *
     * @return Column
     */
    public function row($content)
    {
        if (!$content instanceof \Closure) {
            $row = new Row($content);
        } else {
            $row = new Row();

            call_user_func($content, $row);
        }

        return $this->append($row->build());
    }

    /**
     * Build column html.
     *
     * @return string
     */
    public function build()
    {
        $html = "<div class=\"col-md-{$this->width}\">";

        foreach ($this->contents as $content) {
            if ($content instanceof Renderable || $content instanceof Grid) {
                $html .= $content->render();
            } else {
                $html .= (string) $content;
            }
        }

        return $html.'</div>';
    }

}
