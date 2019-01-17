<?php

namespace Swoft\Admin\Repository;

use Swoft\Admin\Admin;
use Swoft\Admin\Form;
use Swoft\Admin\Grid\Model;
use Swoft\Admin\Show;
use Swoft\Db\Query;
use Swoft\Db\QueryBuilder;

abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * 实体类类名
     *
     * @var string
     */
    protected $entityClass;

    /**
     * 获取主键名称,不填则默认为"id"
     *
     * @return string
     */
    public function getKeyName()
    {
        return Admin::getPrimaryKeyName($this->entityClass);
    }

    /**
     * 网格数据获取接口
     *
     * @param Model $model
     * @return array 返回数组
     *                      如果返回实体类名称, 系统会自动根据实体类查询数据
     * 返回数据示例: [['行内容'...], $total]
     */
    public function find(Model $model)
    {
        // 使用实体单表查询
        $query = Query::table($this->entityClass);
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
        }

        return [&$data, $total];
    }

    /**
     * 详情页数据获取接口
     *
     * @param Show $show
     * @return array 返回数组
     *                      如果返回实体类名称, 系统会自动根据实体类查询数据
     */
    public function findForView(Show $show)
    {
        $entity = $this->entityClass;

        $query = $entity::findById($show->getId())->getResult();

        return $query ? $query->toArray() : [];
    }

    /**
     * 编辑页数据获取接口
     *
     * @param Form $form
     * @return array 返回数组
     *                      如果返回实体类名称, 系统会自动根据实体类查询数据
     */
    public function findForEdit(Form $form)
    {
        $entity = $this->entityClass;

        $query = $entity::findById($form->getId())->getResult();

        return $query ? $query->toArray() : [];
    }

    /**
     * 新增操作
     *
     * @param Form $form
     * @return int 返回新增数据id
     *                    如果返回实体类名称, 系统会自动根据实体类执行新增操作
     */
    public function insert(Form $form)
    {
        return Query::table($this->entityClass)
            ->insert($form->getAttributes())
            ->getResult();
    }

    /**
     * 更新操作
     *
     * @param Form $form
     * @return bool 返回bool值
     */
    public function update(Form $form)
    {
        $entity = $this->entityClass;

        $updates = $form->getAttributes();

        return (bool)$entity::updateOne($updates, [
            $this->getKeyName() => $form->getId()
        ])->getResult();
    }

    /**
     * 删除/批量删除操作
     *
     * @param Form $form
     * @return bool 返回bool值
     */
    public function delete(Form $form)
    {
        $ids   = collect(explode(',', $form->getId()))->filter()->toArray();
        $model = $this->entityClass;

        if (count($ids) == 1) {
            return $model::deleteById($ids[0])->getResult();
        }
        return $model::deleteByIds($ids)->getResult();
    }

    /**
     * 获取旧文件字段值
     * 用于更新或删除后删除旧文件
     *
     * @param mixed $id 单个
     * @return array 返回数组
     */
    public function findForDeleteFiles($id)
    {
        $entity = $this->entityClass;

        $result = $entity::findById($id)->getResult();

        return $result ? $result->toArray() : [];
    }
}
