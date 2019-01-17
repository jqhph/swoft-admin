<?php

namespace Swoft\Admin\Repository;

use Swoft\Admin\Admin;
use Swoft\Admin\Form;
use Swoft\Admin\Grid\Model;
use Swoft\Admin\Show;
use Swoft\Db\QueryBuilder;

class RepositoryProxy implements RepositoryInterface
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 获取主键名称
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->repository->getKeyName() ?: 'id';
    }

    /**
     * 网格数据获取接口
     *
     * @param Model $model
     * @return array
     */
    public function find(Model $model)
    {
        return $this->repository->find($model);
    }

    /**
     * 详情页数据获取接口
     *
     * @param Show $show
     * @return array
     */
    public function findForView(Show $show)
    {
        return $this->repository->findForView($show);
    }

    /**
     * 编辑页数据获取接口
     *
     * @param Form $form
     * @return array
     */
    public function findForEdit(Form $form)
    {
        return $this->repository->findForEdit($form);
    }

    /**
     * 新增操作
     *
     * @param Form $form
     * @return int
     */
    public function insert(Form $form)
    {
        return $this->repository->insert($form);
    }

    /**
     * 更新操作
     *
     * @param Form $form
     * @return bool
     */
    public function update(Form $form)
    {
        return $this->repository->update($form);
    }

    /**
     * 删除/批量删除操作
     *
     * @param Form $form
     * @return bool
     */
    public function delete(Form $form)
    {
        return $this->repository->delete($form);
    }

    /**
     * 获取旧文件字段值
     * 用于更新或删除后删除旧文件
     *
     * @param mixed $id
     * @return array
     */
    public function findForDeleteFiles($id)
    {
        return $this->repository->findForDeleteFiles($id);
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        return $this->repository->$method(...$arguments);
    }
}
