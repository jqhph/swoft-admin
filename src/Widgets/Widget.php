<?php

namespace Swoft\Admin\Widgets;

use Swoft\Support\Fluent;

/**
 *
 * @method $this class($class)
 * @method $this id($id)
 * @method $this style($style)
 */
abstract class Widget extends Fluent
{
    /**
     * @var string
     */
    protected $view;

    /**
     * @return mixed
     */
    abstract public function render();

    /**
     * Set view of widget.
     *
     * @param string $view
     */
    public function view($view)
    {
        $this->view = $view;
    }

    /**
     * 设置属性
     *
     * @param mixed $key
     * @param mixed $value
     * @return $this
     */
    public function attribute($key, $value = null)
    {
        if (is_array($key)) {
            $this->attributes = array_merge($this->attributes, $key);
            return $this;
        }
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * 设置默认属性
     *
     * @param string $attribute
     * @param mixed $value
     * @return $this
     */
    public function defaultAttribute($attribute, $value)
    {
        if (!array_key_exists($attribute, $this->attributes)) {
            $this->attribute($attribute, $value);
        }

        return $this;
    }

    /**
     * 获取属性
     *
     * @return mixed
     */
    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Build an HTML attribute string from an array.
     *
     * @return string
     */
    public function formatAttributes()
    {
        $html = [];
        foreach ((array) $this->getAttributes() as $key => $value) {
            $element = $this->attributeElement($key, $value);
            if (!is_null($element)) {
                $html[] = $element;
            }
        }

        return count($html) > 0 ? ' '.implode(' ', $html) : '';
    }

    /**
     * Build a single attribute element.
     *
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    protected function attributeElement($key, $value)
    {
        if (is_numeric($key)) {
            $key = $value;
        }
        if (!is_null($value)) {
            return $key.'="'.htmlentities($value, ENT_QUOTES, 'UTF-8').'"';
        }
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->render();
    }
}
