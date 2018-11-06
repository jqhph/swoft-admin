<?php

namespace Swoft\Admin;

use Swoft\Core\RequestContext;

class Url
{
    /**
     * @var string
     */
    protected $prefix = '';

    public function __construct()
    {
        $this->initPrefix();
    }

    /**
     * 初始化
     */
    protected function initPrefix()
    {
        $prefix = Admin::getContextAttribute('__url__prefix__');
        if ($prefix || $prefix === false) {
            $this->prefix = $prefix ? $prefix : '/';
            return;
        }

        $prefix = trim(config('admin.route.prefix'), '/');
        $controllerName = Admin::getControllerName();

        $this->prefix = $prefix ? "/{$prefix}/{$controllerName}" : "/$controllerName";
    }

    /**
     * 获取路由前缀
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * 首页
     *
     * @return string
     */
    public function home()
    {
        return admin_base_path('/');
    }

    /**
     * 列表页
     *
     * @return string
     */
    public function list()
    {
        return $this->prefix;
    }

    /**
     * 获取创建按钮链接
     *
     * @return string
     */
    public function create()
    {
        return $this->prefix .'/create';
    }

    /**
     * 查看详情页面(非编辑功能)
     * 需要使用GET方法请求
     *
     * @param string $id
     * @return string
     */
    public function view($id = ':id')
    {
        return $this->prefix.'/view/'.$id;
    }

    /**
     * 编辑页面
     * 需要使用GET方法请求
     *
     * @param string $id
     * @return string
     */
    public function edit($id = ':id')
    {
        return $this->prefix.'/'.$id;
    }

    /**
     * 编辑功能页面
     * 需要使用POST方法请求
     *
     * @param string $id
     * @return string
     */
    public function update($id = ':id')
    {
        return $this->edit($id);
    }

    /**
     * 删除、批量删除功能
     * 需要使用DELETE方法请求
     *
     * @param string $id
     * @return string
     */
    public function delete($id = ':id')
    {
        return $this->prefix.'/'.$id;
    }

    /**
     * 修改单个字段
     * 需要使用post或put方法请求
     *
     * @param string $id
     * @return string
     */
    public function updateField($id = ':id')
    {
        return $this->update($id);
    }

    /**
     * 文件上传
     *
     * @return string
     */
    public function upload()
    {
        return $this->prefix .'/upload';
    }

    /**
     * 获取实例
     *
     * @return static
     */
    public static function make()
    {
        return new static();
    }

}
