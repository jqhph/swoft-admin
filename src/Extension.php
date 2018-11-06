<?php

namespace Swoft\Admin;

use Swoft\Admin\Traits\ExtensionImporter;
use Swoft\Console\Output\Output;
use Swoft\Console\Output\OutputInterface;

abstract class Extension
{
    use ExtensionImporter;

    /**
     * @var static[]
     */
    protected static $instance = [];

    /**
     * 拓展名称
     *
     * @var string
     */
    protected static $name;

    /**
     * 扩展菜单
     * 必须是二维数组, id字段请保持唯一, 一级菜单parent_id写0即可
     *
     * Example:
     * [
     *     [
     *         'id'    => 'ext1',
     *         'title' => 'EXT1',
     *         'path'  => '',
     *         'icon'  => 'fa fa-lg',
     *         'parent_id' => '0',
     *     ],
     *     [
     *         'id'    => 'ext2',
     *         'title' => 'EXT1 CHILD',
     *         'path'  => '/admin/ext1',
     *         'icon'  => '',
     *         'parent_id' => 'ext1',
     *     ]
     * ]
     *
     * @var array
     */
    public $menu = [];

    protected function __construct()
    {
    }

    /**
     * Returns the singleton instance.
     *
     * @return static
     * @throws \Exception
     */
    public static function make()
    {
        if (!static::$name) {
            throw new \Exception("Extension name cannot be empty!");
        }

        return static::$instance[static::$name] ??
            (static::$instance[static::$name] = new static());
    }

    /**
     * 进程启动时触发
     */
    public function boot()
    {
        // 注册扩展
        Admin::extend(static::$name, $this);
    }

    /**
     * 请求处理之前触发
     */
    abstract public function onBeforeRequest();

    /**
     * 请求结束之后触发
     */
    abstract public function onAfterRequest();

    /**
     * 获取扩展静态资源文件目录路径
     * 如不需要,返回空值即可
     *
     * @return string 请返回绝对路径
     */
    abstract public function assets();

    /**
     * 获取扩展模板文件目录路径
     * 如不需要,返回空值即可
     *
     * @return string 请返回绝对路径
     */
    abstract public function views();


    /**
     * 获取数据库迁移文件目录路径
     * 如不需要,返回空值即可
     *
     * @return string 请返回绝对路径
     */
    abstract public function migrations();

    /**
     * 获取语言包目录路径
     * 如不需要,返回空值即可
     *
     * @return string 请返回绝对路径
     */
    abstract public function langs();

    /**
     * 安装扩展时加载
     * 可以导入菜单,静态资源文件,模板文件,语言包等到项目中
     *
     * 如有必要,可以重写此方法
     * @param OutputInterface $output
     */
    public function import(OutputInterface $output = null)
    {
        $output = $output ?: new Output();

        $this->importViews($output);
        $this->importAssets($output);
        $this->importLangs($output);
        $this->importMigrations($output);
    }

    /**
     * 菜单二维数组
     *
     * @return array
     */
    public function menu()
    {
        return $this->menu;
    }

    /**
     * Get config set in config/admin.php.
     *
     * @param string $key
     * @param null   $default
     *
     * @return mixed
     */
    public function config($key, $default = null)
    {
        return config(
            sprintf('admin.extensions.%s.%s', static::$name, $key),
            $default
        );
    }

    /**
     * 获取扩展名称
     *
     * @return string
     */
    public static function getName()
    {
        return static::$name;
    }

}
