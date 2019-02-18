<?php

namespace Swoft\Admin\Widgets;

use Swoft\Support\Contracts\Renderable;

class Dump extends Widget implements Renderable
{
    /**
     * @var string
     */
    protected $view = 'admin::widgets.dump';

    /**
     * @var string
     */
    protected $padding = '10px';

    /**
     * @var string
     */
    protected $content = '';


    /**
     * Dump constructor.
     *
     * @param array|object|string $content
     * @param string|null $padding
     */
    public function __construct($content, string $padding = null)
    {
        $content = $this->getJson($content) ?: $content;

        if ($content instanceof Renderable) {
            $this->content = $content->render();
        } elseif (is_array($content) || is_object($content)) {
            $this->content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            $this->content = $content;
        }
        if ($padding) {
            $this->padding = $padding;
        }
    }

    /**
     * @param mixed $content
     * @return bool
     */
    protected function getJson($content)
    {
        if (!is_string($content)) {
            return false;
        }
        return json_decode($content);
    }

    public function render()
    {
        $this->defaultAttribute('style', 'white-space:pre-wrap');

        return blade($this->view, [
            'attributes' => $this->formatAttributes(),
            'content' => &$this->content,
            'padding' => $this->padding
        ])->render();
    }
}
