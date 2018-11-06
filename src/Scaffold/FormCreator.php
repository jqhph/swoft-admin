<?php

namespace Swoft\Admin\Scaffold;

trait FormCreator
{
    /**
     * @param string $primaryKey
     * @param array $fields
     * @param bool $timestamps
     * @return string
     */
    protected function createFormPrint(string $primaryKey, array $fields, $timestamps)
    {
        $rows = [
            <<<EOF
if (\$id) {
            \$form->display('{$primaryKey}');
        }
EOF

        ];

        foreach ($fields as $field) {
            if ($field['name'] == $primaryKey) continue;

            $rows[] = "        \$form->text('{$field['name']}');";
        }
        if ($timestamps) {
            $rows[] = <<<EOF
        
        \$form->hidden('updated_at');
        if (\$id) {
            \$form->display('created_at');
            \$form->display('updated_at');
        } else {
            // 新增记录的时候防止字段被过滤
            \$form->hidden('created_at');
        }
EOF;
        }

        return implode("\n", $rows);
    }
}
