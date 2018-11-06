<?php

namespace Swoft\Admin\Widgets;

use Swoft\Support\Contracts\Renderable;

class Box extends Widget implements Renderable
{
    /**
     * @var string
     */
    protected $view = 'admin::widgets.box';

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $content = '';

    /**
     * @var array
     */
    protected $tools = [];

    /**
     * Box constructor.
     *
     * @param string $title
     * @param string $content
     */
    public function __construct($title = '', $content = '')
    {
        if ($title) {
            $this->title($title);
        }

        if ($content) {
            $this->content($content);
        }

        $this->class('box');
    }

    /**
     * Set box content.
     *
     * @param string $content
     *
     * @return $this
     */
    public function content($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Set box title.
     *
     * @param string $title
     *
     * @return $this
     */
    public function title($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set box as collapsable.
     *
     * @return $this
     */
    public function collapsable()
    {
        $this->tools[] =
            '<button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>';

        return $this;
    }

    /**
     * Set box as removable.
     *
     * @return $this
     */
    public function removable()
    {
        $this->tools[] =
            '<button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>';

        return $this;
    }

    /**
     *
     * @param string|Renderable|\Closure $content
     * @return $this
     */
    public function tool($content)
    {
        if ($content instanceof Renderable) {
            $content = $content->render();
        } elseif ($content instanceof \Closure) {
            $content = $content($this);
        }

        $this->tools[] = &$content;
        return $this;
    }

    /**
     * Set box style.
     *
     * @param string $styles
     *
     * @return $this|Box
     */
    public function style($styles)
    {
        if (is_string($styles)) {
            return $this->style([$styles]);
        }

        $styles = array_map(function ($style) {
            return 'box-'.$style;
        }, $styles);

        $this->class = $this->class.' '.implode(' ', $styles);

        return $this;
    }

    /**
     * Add `box-solid` class to box.
     *
     * @return $this
     */
    public function solid()
    {
        return $this->style('solid');
    }

    /**
     * Variables in view.
     *
     * @return array
     */
    protected function variables()
    {
        if ($this->content instanceof Renderable) {
            $this->content = $this->content->render();
        } else {
            $this->content = (string) $this->content;
        }

        return [
            'title'      => $this->title,
            'content'    => $this->content,
            'tools'      => $this->tools,
            'attributes' => $this->formatAttributes(),
        ];
    }

    /**
     * Render box.
     *
     * @return string
     */
    public function render()
    {
        return blade($this->view, $this->variables())->render();
    }
}
