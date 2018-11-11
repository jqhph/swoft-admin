<?php

namespace Swoft\Admin\Bean\Parser;

use Swoft\Admin\Bean\Collector\AdminMenuCollector;
use Swoft\Bean\Parser\AbstractParser;

/**
 * The parser of menu
 */
class AdminMenuParser extends AbstractParser
{
    /**
     * @param string  $className
     * @param mixed   $objectAnnotation
     * @param string  $propertyName
     * @param string  $methodName
     * @param null    $propertyValue
     * @return null
     */
    public function parser(
        string $className,
        $objectAnnotation = null,
        string $propertyName = '',
        string $methodName = '',
        $propertyValue = null
    )
    {
        AdminMenuCollector::collect($className, $objectAnnotation, $propertyName, $methodName, $propertyValue);
        return null;
    }
}