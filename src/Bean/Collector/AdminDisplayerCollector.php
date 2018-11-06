<?php

namespace Swoft\Admin\Bean\Collector;

use Swoft\Admin\Grid;
use Swoft\Admin\Bean\Annotation\AdminDisplayer;
use Swoft\Bean\CollectorInterface;

/**
 * The collector displayer
 */
class AdminDisplayerCollector implements CollectorInterface
{
    /**
     * @var array
     */
    private static $values = [];

    /**
     * @param string $className
     * @param object $objectAnnotation
     * @param string $propertyName
     * @param string $methodName
     * @param null   $propertyValue
     *
     * @return mixed
     */
    public static function collect(string $className, $objectAnnotation = null, string $propertyName = '', string $methodName = '', $propertyValue = null)
    {
        if ($objectAnnotation instanceof AdminDisplayer) {
            $value = $objectAnnotation->getName();

            if (! $value) $value = lcfirst(class_basename($className));

            Grid\Column::extend($value, $className);

            self::$values[$className] = $value;
        }

        return null;
    }

    /**
     * @return array
     */
    public static function getCollector()
    {
        return self::$values;
    }
}