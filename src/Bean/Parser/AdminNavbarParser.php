<?php

namespace Swoft\Admin\Bean\Parser;

use Swoft\Admin\Bean\Collector\AdminNavbarCollector;
use Swoft\Bean\Parser\AbstractParser;

/**
 * The parser of navbar
 */
class AdminNavbarParser extends AbstractParser
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
        AdminNavbarCollector::collect($className, $objectAnnotation, $propertyName, $methodName, $propertyValue);
        return null;
    }
}