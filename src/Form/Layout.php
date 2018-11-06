<?php

namespace Swoft\Admin\Form;

use Swoft\Admin\Layout\Column;
use Swoft\Admin\Form;

class Layout
{
    /**
     * @var Form
     */
    protected $form;

    /**
     * @var Column[]
     */
    protected $columns = [];

    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    /**
     * 增加列内容
     *
     * @param int $width 1~12
     * @param $content
     */
    public function column(int $width, $content)
    {
        $width = $width < 1 ? round(12 * $width) : $width;

        $column = new Column($content, $width);

        $this->columns[] = $column;
    }

    /**
     *
     * @param int $width
     * @param $content
     */
    public function prepend(int $width, $content)
    {
        $width = $width < 1 ? round(12 * $width) : $width;

        $column = new Column($content, $width);

        array_unshift($this->columns, $column);
    }

    /**
     * 获取子表单对象
     *
     * @param \Closure|null $callback
     * @return MultipleForm
     */
    public function form(\Closure $callback = null)
    {
       $form = new MultipleForm($this->form->builder());

        $this->form->builder()->addForm($form);

        if ($callback) {
            $callback($form);
        }

        return $form;
    }

    /**
     * Build html of content.
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
