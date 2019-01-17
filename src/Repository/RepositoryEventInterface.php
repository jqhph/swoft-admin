<?php

namespace Swoft\Admin\Repository;

use Swoft\Admin\Form;

interface RepositoryEventInterface
{
    /**
     * @param Form $form
     */
    public function beforeInsert(Form $form);

    /**
     * @param Form $form
     * @param mixed $result 新增数据返回结果
     */
    public function afterInsert(Form $form, $result);

    /**
     * @param Form $form
     */
    public function beforeUpdate(Form $form);

    /**
     * @param Form $form
     * @param mixed $result 编辑数据返回结果
     */
    public function afterUpdate(Form $form, $result);

    /**
     * @param Form $form
     */
    public function beforeDelete(Form $form);

    /**
     * @param Form $form
     * @param $result
     */
    public function afterDelete(Form $form, $result);

}
