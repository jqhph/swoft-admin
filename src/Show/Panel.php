<?php

namespace Swoft\Admin\Show;

use Swoft\Admin\Show;
use Swoft\Support\Collection;
use Swoft\Support\Contracts\Renderable;
use Swoft\Admin\Widgets\Card;

class Panel implements Renderable
{
    /**
     * The view to be rendered.
     *
     * @var string
     */
    protected $view = 'admin::show.panel';

    /**
     * The fields that this panel holds.
     *
     * @var Collection
     */
    protected $fields;

    /**
     * Variables in the view.
     *
     * @var array
     */
    protected $data;

    /**
     * Parent show instance.
     *
     * @var Show
     */
    protected $parent;

    /**
     * Panel constructor.
     */
    public function __construct(Show $show)
    {
        $this->parent = $show;

        $this->initData();
    }

    /**
     * Initialize view data.
     */
    protected function initData()
    {
        $this->data = [
            'fields' => new Collection(),
            'tools'  => new Tools($this),
            'style'  => 'info',
            'title'  => t('Detail', 'admin'),
        ];
    }

    /**
     * Set parent container.
     *
     * @param Show $show
     *
     * @return $this
     */
    public function setParent(Show $show)
    {
        $this->parent = $show;

        return $this;
    }

    /**
     * Get parent container.
     *
     * @return Show
     */
    public function getParent()
    {
        return $this->parent;
    }


    /**
     * Set title for this panel.
     *
     * @param string $title
     *
     * @return $this
     */
    public function title($title)
    {
        $this->data['title'] = $title;

        return $this;
    }

    /**
     * Set view for this panel to render.
     *
     * @param string $view
     *
     * @return $this
     */
    public function view($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Build panel tools.
     *
     * @param $callable
     */
    public function tools($callable)
    {
        call_user_func($callable, $this->data['tools']);
    }

    /**
     * Fill fields to panel.
     *
     * @param []Field $fields
     *
     * @return $this
     */
    public function fill($fields)
    {
        $this->data['fields'] = $fields;

        return $this;
    }

    /**
     * Render this panel.
     *
     * @return string
     */
    public function render()
    {
        $card = new Card($this->data['title'], blade($this->view, $this->data));

        $card->tool($this->data['tools']->render());

        return $card->render();
    }
}
