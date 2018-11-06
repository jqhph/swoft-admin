<?php

namespace Swoft\Admin\Bean\Collector;

use Swoft\Admin\Admin;
use Swoft\Admin\Bean\Annotation\AdminRepository;
use Swoft\Bean\CollectorInterface;

/**
 * The collector Repository
 */
class AdminRepositoryCollector implements CollectorInterface
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
        if ($objectAnnotation instanceof AdminRepository) {
            $value = $objectAnnotation->getValue();

            if ($value) {
                Admin::registerRepository($value, new $className);
            }
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