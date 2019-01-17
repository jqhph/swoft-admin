<?php

namespace Swoft\Admin\Widgets;

class Card extends Box
{
    /**
     * @var string
     */
    protected $view = 'admin::widgets.card';

    /**
     * @var string
     */
    protected $style = '';

    /**
     * @var bool
     */
    protected $divider = true;

    public function __construct($title = '', $content = '')
    {
        parent::__construct($title, $content);

        $this->attribute('class', 'card');
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
        $this->style = $style;
        return $this;
    }

    /**
     * Variables in view.
     *
     * @return array
     */
    protected function variables()
    {
        return [
            'title'      => $this->title,
            'content'    => $this->content,
            'tools'      => $this->tools,
            'attributes' => $this->formatAttributes(),
            'style'      => $this->style,
        ];
    }

}
