<?php

namespace Swoft\Admin;

/**
 * 菜单定义
 */
abstract class AbstractMenu
{
    /**
     * 主键名称
     *
     * @var string
     */
    public $keyName = 'id';

    /**
     * 父级id字段名称
     *
     * @var string
     */
    public $parentId = 'parent_id';

    /**
     * 标题字段名称
     *
     * @var string
     */
    public $title = 'title';

    /**
     * 优先级排序字段名称
     *
     * @var string
     */
    public $priority = 'priority';

    /**
     * 菜单url路径字段名称
     *
     * @var string
     */
    public $path = 'path';

    /**
     * 菜单图标字段名称
     *
     * @var string
     */
    public $icon = 'icon';

    /**
     * 返回菜单节点
     *
     * @return array
     */
    abstract public function fetch(): array;
}

