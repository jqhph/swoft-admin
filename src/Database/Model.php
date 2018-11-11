<?php

namespace Swoft\Admin\Database;

use Swoft\Db\Bean\Collector\EntityCollector;
use Swoft\Helper\StringHelper;

abstract class Model extends \Swoft\Db\Model
{
    /**
     * 转化为数组返回数据表原始字段
     *
     * @return array
     */
    public function toArray(): array
    {
        $entities = EntityCollector::getCollector();
        $columns  = $entities[static::class]['field'];
        $data = [];
        foreach ($columns as $propertyName => $column) {
            if (!isset($column['column'])) {
                continue;
            }
            $methodName = StringHelper::camel('get' . $propertyName);
            if (!\method_exists($this, $methodName)) {
                continue;
            }

            $value = $this->$methodName();
            if($value === null){
                continue;
            }
            $data[$column['column']] = $value;
        }

        return $data;
    }

}
