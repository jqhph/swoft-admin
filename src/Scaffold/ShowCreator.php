<?php

namespace Swoft\Admin\Scaffold;

trait ShowCreator
{
    /**
     * @param string $primaryKey
     * @param array $fields
     * @return string
     */
    protected function createShowPrint(string $primaryKey, array $fields)
    {
        $rows = [];
        foreach ($fields as $k => $field) {
//            if ($field['name'] == $primaryKey) continue;

            $rows[] = "        \$show->{$field['name']};";

            if ($k === 1 && count($fields) > 2) {
                $rows[] = "        \$show->divider();";
            }
        }

        return trim(implode("\n", $rows));
    }
}
