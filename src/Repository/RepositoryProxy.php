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
     * @return array|string
     */
    public function find(Model $model)
    {
        $result = $this->processOriginalResult(
            $this->repository->find($model)
        );

        if (is_array($result)) {
            return $result;
        }

        // 使用实体单表查询
        /* @var QueryBuilder $query */
        $query = $result::query();
        $counter = clone $query;

        $model->getQueries()->each(function (&$value) use ($query, $counter) {
            $method = $value['method'];
            $query->$method(...$value['arguments']);

            if (!in_array($method, ['limit', 'orderBy'])) {
                $counter->$method(...$value['arguments']);
            }
        });
        // 判断是否使用分页
        $total = $model->allowPaginate() ? $counter->count()->getResult() : 1;
        $data = [];
        if ($total) {
            $data = $query->get()->getResult();
            $data  = $data ? $data->toArray() : [];
        }

        return [&$data, $total];
    }

    /**
     * 详情页数据获取接口
     *
     * @param Show $show
     * @return array|string
     */
    public function findForView(Show $show)
    {
        $result = $this->processOriginalResult(
            $this->repository->findForView($show)
        );

        if (is_array($result)) {
            return $result;
        }

        $query = $result::findById($show->getId())->getResult();

        return $query ? $query->toArray() : [];
    }

    /**
     * 编辑页数据获取接口
     *
     * @param Form $form
     * @return array|string
     */
    public function findForEdit(Form $form)
    {
        $result = $this->processOriginalResult(
            $this->repository->findForEdit($form)
        );

        if (is_array($result)) {
            return $result;
        }

        $query = $result::findById($form->getId())->getResult();

        return $query ? $query->toArray() : [];
    }

    /**
     * 新增操作
     *
     * @param Form $form
     * @return int|string
     */
    public function insert(Form $form)
    {
        $result = $this->repository->insert($form);

        if (is_int($result) || is_numeric($result)) {
            return (int)$result;
        }

        if (is_string($result) && class_exists($result)) {
            return (int)Admin::getModel($result)
                ->fill($form->getAttributes())
                ->save()
                ->getResult();
        }

        return 0;
    }

    /**
     * 更新操作
     *
     * @param Form $form
     * @return bool|string
     */
    public function update(Form $form)
    {
        $result = $this->repository->update($form);
        if (is_bool($result) || is_numeric($result)) {
            return (bool)$result;
        }

        if (is_string($result) && class_exists($result)) {
            $updates = $form->getAttributes();

            return (bool)$result::updateOne($updates, [Admin::getPrimaryKeyName($result) => $form->getId()])->getResult();
        }

        return false;
    }

    /**
     * 删除/批量删除操作
     *
     * @param Form $form
     * @return bool|string
     */
    public function delete(Form $form)
    {
        $result = $this->repository->delete($form);

        if (is_bool($result) || is_numeric($result)) {
            return (bool)$result;
        }
        if (is_string($result) && class_exists($result)) {
            $ids   = collect(explode(',', $form->getId()))->filter()->toArray();
            $model = get_class(Admin::getModel($result));

            if (count($ids) == 1) {
                return (bool)$model::deleteById($ids[0])->getResult();
            }
            return (bool)$model::deleteByIds($ids)->getResult();
        }
        return false;
    }

    /**
     * 获取旧文件字段值
     * 用于更新或删除后删除旧文件
     *
     * @param mixed $id
     * @return array|string
     */
    public function findForDeleteFiles($id)
    {
        $result = $this->processOriginalResult(
            $this->repository->findForDeleteFiles($id)
        );
        if (is_array($result)) {
            return $result;
        }
        $model = Admin::getModel($result);

        $result = $result::findById($id)->getResult();

        return $result ? $result->toArray() : [];
    }

    /**
     * @param $result
     * @return array|string
     */
    protected function processOriginalResult($result)
    {
        if (is_array($result)) {
            return $result;
        }
        if (!is_string($result) || !$result) {
            return [];
        }
        $entity = Admin::getModel($result);

        return $result;
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
