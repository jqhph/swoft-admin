<?php

namespace Swoft\Admin\Scaffold;

use Swoft\Admin\Database\Model;
use Swoft\App;
use Swoft\Db\Entity\Mysql\Schema;
use Swoft\Db\Helper\DbHelper;
use Swoft\Db\Pool;
use Swoft\Db\Pool\DbPool;

class ModelCreator
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var \Swoft\Db\Entity\Schema $schema schema对象
     */
    private $schema;

    /**
     * @var EntityGenerator $generatorEntity 实体实例
     */
    private $generatorEntity;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * @param string $modelClass
     * @param string $database
     * @param string $removeTablePrefix
     * @return string
     */
    public function create(string $modelClass, string $database, string $removeTablePrefix = '')
    {
        if (class_exists($modelClass)) {
            return t("Entity(%s) already exists!", 'admin.scaffold', [$modelClass]);
        }

        $instance = '';
        $extends = null;
        $tablesDisabled = [];

        $this->setEntityFilePath($modelClass);

        $this->initDatabase($instance);

        if (empty($database)) {
            return t('Databases cant not be empty!', 'admin.scaffold');
        }

        $this->generatorEntity->db = $database;
        $this->generatorEntity->instance = $instance;
        $this->generatorEntity->tablesEnabled = [$this->table];
        $this->generatorEntity->tablesDisabled = $tablesDisabled;
        $this->generatorEntity->removeTablePrefix = $removeTablePrefix;
        // 设置实体基类
//        $this->generatorEntity->setExtends('\\'.Model::class);
        if ($extends) $this->generatorEntity->setExtends($extends);

        $this->generatorEntity->execute($this->schema);

        return t("Model(%s) save succeeded!", 'admin.scaffold', [$modelClass]);
    }

    /**
     * 设置实体生成路径
     */
    private function setEntityFilePath(string $modelClass)
    {
        $modelClass = explode("\\", $modelClass);
        array_pop($modelClass);

        $path = App::getAlias(str_replace('App', '@app', implode('/', $modelClass)));

        if (!is_dir($path)) {
            filesystem()->mkdir($path);
        }

        App::setAlias('@entityPath', $path);
    }

    /**
     * 初始化方法
     */
    private function initDatabase($instance = Pool::INSTANCE): bool
    {
        $instance = $instance ?: Pool::INSTANCE;
        $pool = DbHelper::getPool($instance, Pool::MASTER);

        $schema = new Schema();
        $schema->setDriver('MYSQL');
        $this->schema = $schema;
        $this->generatorEntity = new EntityGenerator($pool->createConnection());

        return true;
    }
}
