<?php

namespace Swoft\Admin\Layout;

class Row implements Buildable
{
    /**
     * @var Column[]
     */
    protected $columns = [];

    /**
     * Row constructor.
     *
     * @param string $content
     */
    public function __construct($content = '')
    {
        if (!empty($content)) {
            $this->column(12, $content);
        }
    }

    /**
     * Add a column.
     *
     * @param int $width
     * @param $content
     */
    public function column(int $width, $content)
    {
        $width = $width < 1 ? round(12 * $width) : $width;

        $column = new Column($content, $width);

        $this->addColumn($column);
    }

    /**
     * @param Column $column
     */
    protected function addColumn(Column $column)
    {
        $this->columns[] = $column;
    }

    /**
     * Build row column.
     *
     * @return string
     */
    public function build()
    {
        $html = '<div class="row">';

        foreach ($this->columns as $column) {
            $html .= $column->build();
        }

        return $html.'</div>';
    }

}
