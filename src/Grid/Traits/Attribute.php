<?php

namespace Swoft\Admin\Grid\Traits;

trait Attribute
{
    /**
     * @var array
     */
    protected $_attr = [];

    /**
     * 设置属性
     *
     * @param $key
     * @param null $val
     * @return $this
     */
    public function setAttribute($key, $val = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => &$v) {
                $this->setAttribute($k, $v);
            }
            return $this;
        }

        $this->_attr[$key] = &$val;
        return $this;

    }

    /**
     * 获取属性
     *
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function getAttribute(string $key, $default = null)
    {
        return array_get($this->_attr, $key, $default);
    }

    /**
     * @return bool
     */
    public function hasAttribute(string $key)
    {
        return array_has($this->_attr, $key);
    }


    /**
     * 获取属性值并移除
     *
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function pullAttribute(string $key, $default = null)
    {
        return array_pull($this->_attr, $key, $default);
    }
}
