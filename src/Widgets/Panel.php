<?php

namespace Swoft\Admin\Widgets;

use Swoft\Support\Contracts\Renderable;

class Panel extends Widget implements Renderable
{
    /**
     * @var string
     */
    protected $view = 'admin::widgets.panel';

    /**
     * @var array
     */
    protected $items = [];

    public function __construct()
    {
        $this->class('panel-group');
        $this->id('p'.uniqid());
    }

    /**
     * 增加选项
     *
     * @param string $title
     * @param string|\Closure|Renderable $content
     * @param bool $show 默认是否展开内容
     * @param string $style 支持 default, primary, success, warning, info
     * @return $this
     */
    public function add(
        string $title,
        $content,
        bool $show = false,
        string $style = 'default'
    )
    {
        $this->items[] = [
            'title'   => $title,
            'content' => $this->normalize($content),
            'style'   => $style,
            'show'    => $show ? 'in' : 'out',
        ];

        return $this;
    }

    /**
     * @param $content
     * @return mixed
     */
    protected function normalize($content)
    {
        if ($content instanceof \Closure) {
            $content = $content();
        }

        if ($content instanceof Renderable) {
            return $content->render();
        }

        return $content;
    }

    protected function var()
    {
        return [
            'attributes' => $this->formatAttributes(),
            'items'      => &$this->items,
        ];
    }

    public function render()
    {
        return blade($this->view, $this->var())->render();
    }
}
