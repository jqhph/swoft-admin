<?php

namespace Swoft\Admin\Form\Field;

use Swoft\Admin\Form\Field;

class Html extends Field
{
    /**
     * Htmlable.
     *
     * @var string|\Closure
     */
    protected $html = '';

    /**
     * @var string
     */
    protected $label = '';

    /**
     * @var bool
     */
    protected $plain = false;

    /**
     * Create a new Html instance.
     *
     * @param mixed $html
     * @param array $arguments
     */
    public function __construct($html, $arguments)
    {
        $this->html = $html;

        $this->label = array_get($arguments, 0);
    }

    /**
     * @return $this
     */
    public function plain()
    {
        $this->plain = true;

        return $this;
    }

    /**
     * 设置字段名称
     *
     * @param string $column
     * @return $this
     */
    public function setColumn(string $column)
    {
        $this->column = $column;
        return $this;
    }

    /**
     * Render html field.
     *
     * @return string
     */
    public function render()
    {
        if ($this->html instanceof \Closure) {
            $this->html = call_user_func($this->html, $this);
        }

        if ($this->plain) {
            return $this->html;
        }

        $this->addVariables([
            'html' => $this->html
        ]);

        return parent::render();
    }
}
