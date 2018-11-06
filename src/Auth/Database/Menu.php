<?php

namespace Swoft\Admin\Auth\Database;

use Swoft\Admin\AbstractMenu;
use Swoft\Admin\Admin;
use Swoft\Admin\Bean\Collector\AdminMenuCollector;
use Swoft\Admin\Traits\AdminBuilder;
use Swoft\Admin\Traits\ModelTree;
use Swoft\Db\Model;
use Swoft\Exception\ValidatorException;
use Swoft\Support\Validator;

/**
 * Class Menu.
 *
 */
class Menu
{
    use AdminBuilder, ModelTree;

    /**
     * 拓展插件的菜单
     *
     * @var array
     */
    protected static $extension = [];

    /**
     * 菜单插件验证规则
     *
     * @var array
     */
    protected static $extensioMenuValidationRules = [
        'id'    => 'required',
        'title' => 'required',
        'parent_id' => 'required',
    ];

    /**
     * @var array
     */
    protected $nodes = [];

    public function __construct()
    {
        $this->initNodes();
    }

    /**
     * 初始化菜单节点
     *
     * @return array
     */
    protected function initNodes()
    {
        $nodes = $this->fetchNodes();

        foreach (static::$extension as $name => &$extNodes) {
            $nodes = array_merge($nodes, $extNodes);
        }

        $this->setNodes($nodes);
    }

    /**
     * 获取菜单节点
     *
     * @return array
     */
    protected function fetchNodes()
    {
        $menuClass = AdminMenuCollector::getCollector();
        if (!$menuClass || !class_exists($menuClass)) {
            return [];
        }
        /* @var AbstractMenu $menu */
        $menu = new $menuClass;

        if (!$menu instanceof AbstractMenu) {
            throw new \UnexpectedValueException("$menuClass 必须继承 ".AbstractMenu::class);
        }

        static::$primaryColumn = $menu->keyName;
        static::$titleColumn   = $menu->title;
        static::$parentColumn  = $menu->parentId;
        static::$pathColumn    = $menu->path;
        static::$orderColumn   = $menu->priority;
        static::$iconColumn    = $menu->icon;

        return $menu->fetch();
    }

    /**
     *
     * @return Model
     */
    public static function getModel()
    {
        return Admin::getModel(config('admin.database.menu_model'));
    }

    /**
     * @param array $nodes
     */
    public function setNodes(array $nodes)
    {
        $this->nodes = &$nodes;
    }

    /**
     * 导入插件菜单
     *
     * @param string $name
     * @param array $nodes
     */
    public static function importExtension(string $name, array $nodes)
    {
        $new = [];
        foreach ($nodes as &$node) {
            static::validateExtensionMenu($node);

            $new[] = [
                static::$primaryColumn => $node['id'],
                static::$titleColumn => $node['title'],
                static::$parentColumn => $node['parent_id'],
                static::$pathColumn => array_get($node, 'path'),
                static::$iconColumn => array_get($node, 'icon'),
                static::$orderColumn => array_get($node, 'priority', 9999)
            ];
        }

        static::$extension[$name] = $new;
    }

    /**
     * Validate menu fields.
     *
     * @param array $menu
     *
     * @throws \Exception
     *
     * @return bool
     */
    public static function validateExtensionMenu($menu)
    {
        if (!is_array($menu)) {
            throw new ValidatorException("Invalid menu");
        }

        $validator = Validator::make(static::$extensioMenuValidationRules);

        $validator->batch(true);

        if ($validator->check($menu)) {
            return true;
        }

        $message = "Invalid menu: ".implode("; ", array_flatten($validator->getError()));

        throw new ValidatorException($message);
    }

    /**
     * @return array
     */
    public function allNodes(): array
    {
        return $this->nodes;
    }


}
