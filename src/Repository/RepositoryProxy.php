<?php

namespace Swoft\Admin\Repository;

use Swoft\Admin\Admin;
use Swoft\Admin\Bean\Collector\AdminRepositoryListenerCollector;
use Swoft\Admin\Form;
use Swoft\Admin\Grid\Model;
use Swoft\Admin\Show;
use Swoft\Db\QueryBuilder;

class RepositoryProxy implements RepositoryInterface
{
    /**
     * @var string
     */
    protected $repository;

    /**
     * @var []
     */
    protected $listeners = [];

    public function __construct(string $repository)
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
        return $this->getRepository()->getKeyName() ?: 'id';
    }

    /**
     * 网格数据获取接口
     *
     * @param Model $model
     * @return array
     */
    public function find(Model $model)
    {
        return $this->getRepository()->find($model);
    }

    /**
     * 详情页数据获取接口
     *
     * @param Show $show
     * @return array
     */
    public function findForView(Show $show)
    {
        return $this->getRepository()->findForView($show);
    }

    /**
     * 编辑页数据获取接口
     *
     * @param Form $form
     * @return array
     */
    public function findForEdit(Form $form)
    {
        return $this->getRepository()->findForEdit($form);
    }

    /**
     * 新增操作
     *
     * @param Form $form
     * @return int
     */
    public function insert(Form $form)
    {
        $listeners = $this->getListeners();
        foreach ($listeners as $listener) {
            $listener->beforeInsert($form);
        }

        $result = $this->getRepository()->insert($form);

        foreach ($listeners as $listener) {
            $listener->afterInsert($form, $result);
        }

        return $result;
    }

    /**
     * 更新操作
     *
     * @param Form $form
     * @return bool
     */
    public function update(Form $form)
    {
        $listeners = $this->getListeners();
        foreach ($listeners as $listener) {
            $listener->beforeUpdate($form);
        }

        $result = $this->getRepository()->update($form);

        foreach ($listeners as $listener) {
            $listener->afterUpdate($form, $result);
        }

        return $result;
    }

    /**
     * 删除/批量删除操作
     *
     * @param Form $form
     * @return bool
     */
    public function delete(Form $form)
    {
        $listeners = $this->getListeners();
        foreach ($listeners as $listener) {
            $listener->beforeDelete($form);
        }

        $result = $this->getRepository()->delete($form);

        foreach ($listeners as $listener) {
            $listener->afterDelete($form, $result);
        }

        return $result;
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
        return $this->getRepository()->findForDeleteFiles($id);
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        return $this->getRepository()->$method(...$arguments);
    }

    /**
     * @return RepositoryInterface
     */
    protected function getRepository()
    {
        return \bean($this->repository);
    }

    /**
     * @return RepositoryEventInterface[]
     * @throws InvalidRepositoryListenerException
     */
    protected function getListeners()
    {
        if (isset($this->listeners[$this->repository])) {
            return $this->listeners[$this->repository];
        }

        $listeners = [];
        foreach (AdminRepositoryListenerCollector::getCollector($this->repository) as $beanName) {
            /* @var RepositoryEventInterface $listener */
            $listener = \bean($beanName);
            if (!$listener instanceof RepositoryEventInterface) {
                throw new InvalidRepositoryListenerException("$beanName 必须实现 ".RepositoryEventInterface::class.' 接口');
            }

            $listeners[] = $listener;
        }

        return $this->listeners[$this->repository] = $listeners;
    }
}
