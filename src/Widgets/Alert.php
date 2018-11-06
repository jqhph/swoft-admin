<?php

namespace Swoft\Admin\Widgets;

use Swoft\Support\Contracts\Renderable;

class Alert extends Widget implements Renderable
{
    /**
     * @var string
     */
    protected $view = 'admin::widgets.alert';

    /**
     * @var string|\Symfony\Component\Translation\TranslatorInterface
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $content = '';

    /**
     * @var string
     */
    protected $style = 'danger';

    /**
     * @var string
     */
    protected $icon = 'ban';

    /**
     * Alert constructor.
     *
     * @param mixed  $content
     * @param string $title
     */
    public function __construct($content, $title = '')
    {
        $this->content = (string) $content;

        $this->title = $title ?: t('Aalert', 'admin');

        $this->style('danger');
    }

    /**
     * Add style.
     *
     * @param string $style
     *
     * @return $this
     */
    public function style($style = 'info')
    {
        $this->style = $style;

        return $this;
    }

    public function info()
    {
        return $this->style('info')->icon('fa fa-info');
    }

    public function success()
    {
        return $this->style('success')->icon('icon fa fa-check');
    }

    public function warning()
    {
        return $this->style('warning')->icon('fa fa-warning');
    }

    /**
     * Add icon.
     *
     * @param string $icon
     *
     * @return $this
     */
    public function icon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return array
     */
    protected function variables()
    {
        $this->class("alert alert-{$this->style} alert-dismissable");

        return [
            'title'      => $this->title,
            'content'    => $this->content,
            'icon'       => $this->icon,
            'attributes' => $this->formatAttributes(),
        ];
    }

    /**
     * Render alter.
     *
     * @return string
     */
    public function render()
    {
        return blade($this->view, $this->variables())->render();
    }
}
