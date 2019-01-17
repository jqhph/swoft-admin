<?php

namespace Swoft\Admin\Bean\Collector;

use Swoft\Admin\Bean\Annotation\AdminRepositoryListener;
use Swoft\Bean\CollectorInterface;

/**
 * The collector AdminRepositoryListener
 */
class AdminRepositoryListenerCollector implements CollectorInterface
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
    public static function collect(
        string $className,
        $objectAnnotation = null,
        string $propertyName = '',
        string $methodName = '',
        $propertyValue = null
    )
    {
        if ($objectAnnotation instanceof AdminRepositoryListener) {
            $value = $objectAnnotation->getValue();
            if (!isset(self::$values[$value])) {
                self::$values[$value] = [];
            }

            self::$values[$value][] = $className;
        }

        return null;
    }

    /**
     * @return array
     */
    public static function getCollector(string $repository = null)
    {
        if ($repository) {
            return isset(self::$values[$repository]) ? self::$values[$repository] : [];
        }

        return self::$values;
    }
}