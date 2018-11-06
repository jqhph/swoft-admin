<?php

namespace Swoft\Admin\Widgets;

class Card extends Box
{
    /**
     * @var string
     */
    protected $view = 'admin::widgets.card';

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
     * @return $this
     */
    public function disabledDivider()
    {
        $this->divider = false;
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
            'divider'    => $this->divider,
        ];
    }

}
