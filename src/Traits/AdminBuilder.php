<?php

namespace Swoft\Admin\Traits;

use Swoft\Admin\Form;
use Swoft\Admin\Grid;
use Swoft\Admin\Tree;

trait AdminBuilder
{
    /**
     * @param \Closure $callback
     *
     * @return Grid
     */
    public static function grid(\Closure $callback)
    {
        return new Grid(self::getModel(), $callback);
    }

    /**
     * @param \Closure $callback
     *
     * @return Form
     */
    public static function form(\Closure $callback)
    {
//        Form::registerBuiltinFields();

        return new Form(self::getModel(), $callback);
    }

    /**
     * @param \Closure $callback
     *
     * @return Tree
     */
    public static function tree(\Closure $callback = null)
    {
        return new Tree(self::getModel(), $callback);
    }
}
