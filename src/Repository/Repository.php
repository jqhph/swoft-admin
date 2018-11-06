<?php

namespace Swoft\Admin\Repository;

use Swoft\Core\RequestContext;

trait Repository
{
    /**
     * @var array
     */
    protected static $repositories = [];

    /**
     * 获取当前控制器对应的数据仓库
     *
     * @return RepositoryInterface
     */
    public static function repository()
    {
        $controller = RequestContext::getContextDataByKey('controllerClass');

        if (isset(static::$repositories[$controller])) {
            return static::$repositories[$controller];
        }

        throw new \UnexpectedValueException("Repository未定义");
    }

    /**
     * 注册Repository
     *
     * @param string $controllerClass
     * @param RepositoryInterface $repository
     */
    public static function registerRepository(string $controllerClass, RepositoryInterface $repository)
    {
        static::$repositories[$controllerClass] = new RepositoryProxy($repository);
    }
}
