<?php

namespace Swoft\Admin\Repository;

use Swoft\Admin\Form;
use Swoft\Admin\Grid\Model;
use Swoft\Admin\Show;

/**
 * 数据层接口
 * @package Swoft\Admin\Repository
 */
interface RepositoryInterface
{
    /**
     * 获取主键名称,不填则默认为"id"
     *
     * @return string
     */
    public function getKeyName();

    /**
     * 网格数据获取接口
     *
     * @param Model $model
     * @return array 返回数组
     *
     * 返回数据示例: [['行内容'...], $total]
     */
    public function find(Model $model);

    /**
     * 详情页数据获取接口
     *
     * @param Show $show
     * @return array 返回数组
     */
    public function findForView(Show $show);

    /**
     * 编辑页数据获取接口
     *
     * @param Form $form
     * @return array 返回数组
     */
    public function findForEdit(Form $form);

    /**
     * 新增操作
     *
     * @param Form $form
     * @return int 返回新增数据id
     */
    public function insert(Form $form);

    /**
     * 更新操作
     *
     * @param Form $form
     * @return bool 返回bool值
     */
    public function update(Form $form);

    /**
     * 删除/批量删除操作
     *
     * @param Form $form
     * @return bool 返回bool值
     */
    public function delete(Form $form);

    /**
     * 获取旧文件字段值
     * 用于更新或删除后删除旧文件
     *
     * @param mixed $id 单个
     * @return array 返回数组
     */
    public function findForDeleteFiles($id);
}
