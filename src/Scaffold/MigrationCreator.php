<?php

namespace Swoft\Admin\Scaffold;

use Swoft\Migrations\Console\Application;

class MigrationCreator
{
    /**
     * @var array
     */
    public static $types = [
        'INTEGER' => [
            'integer',
            'boolean',
            'tinyInteger',
            'smallInteger',
            'mediumInteger',
            'bigInteger',
            'unsignedInteger',
            'unsignedTinyInteger',
            'unsignedSmallInteger',
            'unsignedMediumInteger',
            'unsignedBigInteger'
        ],
        'STRING' => [
            'string',
            'char',
        ],
        'TEXT' => [
            'text',
            'tinyText',
            'smallText',
            'mediumText',
            'longText',
        ],
        'FLOAT' => [
            'float',
            'decimal'
        ],
        'TIME' => [
            'time',
            'date',
            'datetime',
            'timestamp'
        ],
        'OTHER' => [
            'enum',
            'json',
            'jsonb',
            'binary'
        ],
    ];

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->table = $table;
        $this->filename = 'Scaffold'.ucfirst(camel__case($table));
    }

    /**
     *
     * @param string $primaryKey
     * @param array $fields
     * @param bool $timestamps
     * @param bool $softDeletes
     * @return string
     */
    public function create(string $primaryKey, array $fields, bool $timestamps, bool $softDeletes)
    {
        return Application::call(
            'migrate:create',
            $this->filename,
            '--code',
            $this->buildMigrationPrint($primaryKey, $fields, $timestamps, $softDeletes)
        );
    }

    /**
     *
     * @param string $primaryKey
     * @param array $fields
     * @param bool $timestamps
     * @param bool $softDeletes
     * @return string
     */
    protected function buildMigrationPrint(string $primaryKey, array $fields, bool $timestamps, bool $softDeletes)
    {
        $indexes = [];

        $columns = [];

        $definedPk = false;
        foreach ($fields as $field) {
            if (empty($field['type'])) {
                continue;
            }
            $name = (string)$field['name'];
            $type = (string)$field['type'];
            $key  = (string)$field['key'];
            $default = (string)$field['default'];
            $comment = (string)$field['comment'];
            $nullable = (bool)array_get($field, 'nullable');

            $columns[] = $this->formatColumnMethodPrint($primaryKey, $name, $type, $nullable, $default, $comment);

            if ($key && $primaryKey != $name) {
                $indexes[$name] = $key;
            }

            if ($primaryKey && $primaryKey == $name) {
                $definedPk = true;
            }
        }

        if ($timestamps) {
            $columns[] = $this->formatColumnMethodPrint($primaryKey, 'created_at', 'timestamp', true, '', '');
            $columns[] = $this->formatColumnMethodPrint($primaryKey, 'updated_at', 'timestamp', true, '', '更新时间');
        }
        if ($softDeletes) {
            $columns[] = $this->formatColumnMethodPrint($primaryKey, 'is_deleted', 'boolean', false, '0', '是否删除');
        }
        if (!$definedPk && $primaryKey) {
            // 主键字段未定义
            array_unshift($columns, $this->formatColumnMethodPrint($primaryKey, $primaryKey, 'integer', false, '', '主键'));
        }

        $columns = implode("\n", $columns);
        $indexes = $this->formatIndexes($indexes);

        // 定义了主键字段
        $addPk = '';
        if ($primaryKey) {
            $addPk = "->setPrimaryKey('{$primaryKey}')";
        }

        return <<<EOF
\$this->tableProxy('{$this->table}', function (TableProxy \$table) {
$columns

$indexes

            \$table->setId(false)
                $addPk
                ->innodb()
                ->create();
        });
EOF;

    }

    /**
     *
     * @param array $indexes
     * @return string
     */
    protected function formatIndexes(array $indexes)
    {
        $new = [];
        foreach ($indexes as $field => $index) {
            $string = "            \$table->addIndex('$field')";

            if ($index == 'unique') {
                $string .= "->unique()";
            }

            $new[] = $string.';';
        }

        return implode("\n", $new);
    }

    /**
     * @param string $primaryKey
     * @param string $name
     * @param string $type
     * @param bool $nullable
     * @param string $default
     * @param string $comment
     * @return string
     */
    protected function formatColumnMethodPrint(
        string $primaryKey,
        string $name,
        string $type,
        bool $nullable,
        string $default,
        string $comment
    )
    {
        $string = '            $table->';

        switch ($type) {
            case 'string':
            case 'char':
            case 'integer':
            case 'text':
            case 'float':
            case 'decimal':
            case 'boolean':
            case 'date':
            case 'time':
            case 'datetime':
            case 'timestamp':
            case 'enum':
            case 'json':
            case 'jsonb':
            case 'binary':
                $string .= "$type('$name')";
                break;
            case 'tinyInteger':
                $string .= "integer('$name')->tiny()";
                break;
            case 'smallInteger':
                $string .= "integer('$name')->small()";
                break;
            case 'mediumInteger':
                $string .= "integer('$name')->medium()";
                break;
            case 'bigInteger':
                $string .= "integer('$name')->big()";
                break;
            case 'unsignedInteger':
                $string .= "integer('$name')->unsigned()";
                break;
            case 'unsignedTinyInteger':
                $string .= "integer('$name')->tiny()->unsigned()";
                break;
            case 'unsignedSmallInteger':
                $string .= "integer('$name')->small()->unsigned()";
                break;
            case 'unsignedMediumInteger':
                $string .= "integer('$name')->medium()->unsigned()";
                break;
            case 'unsignedBigInteger':
                $string .= "integer('$name')->big-()>unsigned()";
                break;
            case 'tinyText':
                $string .= "text('$name')->tiny()";
                break;
            case 'smallText':
                $string .= "text('$name')->small()";
                break;
            case 'mediumText':
                $string .= "text('$name')->medium()";
                break;
            case 'longText':
                $string .= "text('$name')->long()";
                break;

        }

        $ints = ['integer', 'mediumInteger', 'bigInteger', 'unsignedInteger', 'unsignedMediumInteger', 'unsignedBigInteger'];
        if ($primaryKey == $name && in_array($type, $ints)) {
            // int类型且为主键,则自动设置为自增
            $string .= "->autoincrement()->unsigned()";
            $nullable = false;
            $default  = false;
        }

        if ($nullable && !$default && $default !== '0') {
            $string .= "->null()->default(null)";
        } elseif ($nullable && ($default || $default === '0')) {
            $string .= "->null()->default('$default')";
        } elseif (($default || $default === '0')) {
            $string .= "->default('$default')";
        }

        if ($comment) {
            $comment = str_replace("'", "\\'", $comment);
            $string .= "->comment('$comment')";
        }

        return $string.';';
    }

}
