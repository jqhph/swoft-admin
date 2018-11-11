<?php

namespace Swoft\Admin\Scaffold;

trait GridCreator
{
    /**
     * @param string $primaryKey
     * @param array $fields
     * @return string
     */
    protected function createGridPrint(string $primaryKey, array $fields)
    {
        $rows = [
            "\$grid->{$primaryKey}->sortable()->desc();"
        ];

        foreach ($fields as $field) {
            if ($field['name'] == $primaryKey) continue;

            $rows[] = "        \$grid->{$field['name']};";
        }
        $rows[] = <<<EOF
        
        \$grid->filter(function (Grid\Filter \$filter) {
            \$filter->equal('$primaryKey');
        
        });
EOF;


        return implode("\n", $rows);
    }
}
