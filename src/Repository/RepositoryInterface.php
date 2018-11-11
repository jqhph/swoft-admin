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
     * @return array|string 返回数组或实体类名称
     *                      如果返回实体类名称, 系统会自动根据实体类查询数据
     * 返回数据示例: [['行内容'...], $total]
     */
    public function find(Model $model);

    /**
     * 详情页数据获取接口
     *
     * @param Show $show
     * @return array|string 返回数组或实体类名称
     *                      如果返回实体类名称, 系统会自动根据实体类查询数据
     */
    public function findForView(Show $show);

    /**
     * 编辑页数据获取接口
     *
     * @param Form $form
     * @return array|string 返回数组或实体类名称
     *                      如果返回实体类名称, 系统会自动根据实体类查询数据
     */
    public function findForEdit(Form $form);

    /**
     * 新增操作
     *
     * @param Form $form
     * @return int|string 返回新增数据id或实体类名称
     *                    如果返回实体类名称, 系统会自动根据实体类执行新增操作
     */
    public function insert(Form $form);

    /**
     * 更新操作
     *
     * @param Form $form
     * @return bool|string 返回bool值或实体类名称
     *                     如果返回实体类名称, 系统会自动根据实体类执行更新操作
     */
    public function update(Form $form);

    /**
     * 删除/批量删除操作
     *
     * @param Form $form
     * @return bool|string 返回bool值或实体类名称
     *                     如果返回实体类名称, 系统会自动根据实体类执行删除操作
     */
    public function delete(Form $form);

    /**
     * 获取旧文件字段值
     * 用于更新或删除后删除旧文件
     *
     * @param mixed $id 单个
     * @return array|string 返回数组或实体类名称
     *                      如果返回实体类名称, 系统会自动根据实体类查询数据
     */
    public function findForDeleteFiles($id);
}
