<?php

namespace Swoft\Admin\Traits;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Swoft\Admin\Admin;
use Swoft\Admin\Form\Builder;
use Swoft\Admin\Form\Field;
use Swoft\Admin\Redirector;
use Swoft\Http\Message\Upload\UploadedFile;
use Swoft\Support\Collection;
use Swoft\Support\Url;
use Swoft\Support\Validator;

trait FormModel
{
    protected static $errors = array(
        UPLOAD_ERR_INI_SIZE => 'The file "%s" exceeds your upload_max_filesize ini directive (limit is %d KiB).',
        UPLOAD_ERR_FORM_SIZE => 'The file "%s" exceeds the upload limit defined in your form.',
        UPLOAD_ERR_PARTIAL => 'The file "%s" was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_CANT_WRITE => 'The file "%s" could not be written on disk.',
        UPLOAD_ERR_NO_TMP_DIR => 'File could not be uploaded: missing temporary directory.',
        UPLOAD_ERR_EXTENSION => 'File upload was stopped by a PHP extension.',
    );

    /**
     * Ignored saving fields.
     *
     * @var array
     */
    protected $ignored = [];

    /**
     * Submitted callback.
     *
     * @var Closure[]
     */
    protected $submitted = [];

    /**
     * Saving callback.
     *
     * @var Closure[]
     */
    protected $saving = [];

    /**
     * Saved callback.
     *
     * @var Closure[]
     */
    protected $saved = [];

    /**
     * Data for save to current model from input.
     *
     * @var array
     */
    protected $updates = [];

    /**
     * @var mixed
     */
    protected $newId;

    /**
     * 修改成功返回ture
     *
     * @var bool
     */
    protected $updated = null;

    /**
     * 获取新增id
     *
     * @return mixed
     */
    public function getNewId()
    {
        return $this->newId;
    }

    /**
     * Set submitted callback.
     *
     * @param Closure $callback
     * @return $this
     */
    public function submitted(Closure $callback)
    {
        $this->submitted[] = $callback;
        return $this;
    }

    /**
     * Set saving callback.
     *
     * @param Closure $callback
     * @return $this
     */
    public function saving(Closure $callback)
    {
        $this->saving[] = $callback;
        return $this;
    }

    /**
     * Set saved callback.
     *
     * @param Closure $callback
     * @return $this
     */
    public function saved(Closure $callback)
    {
        $this->saved[] = $callback;
        return $this;
    }

    /**
     * 获取待新增或编辑的数据
     *
     * @param bool $original 是否把字段名称转化为下划线风格
     * @return array
     */
    public function getAttributes(bool $original = false)
    {
        if ($original) {
            return $this->convertOriginalDbFields($this->updates);
        }
        return $this->updates;
    }

    /**
     * 新增记录
     *
     * @return Redirector
     * @return \UnexpectedValueException
     */
    public function insert()
    {
        $data = $this->input->all();
        admin_debug('[创建]原始表单数据', $data);

        $this->checkFileInput($data);

        // Handle validation errors.
        if ($validationMessages = $this->validationMessages($data)) {
            flash_errors($validationMessages);
            flash_input($data);

            return new Redirector(false);
        }

        $this->prepare($data);

        $this->updates = $this->prepareInsert($this->updates);
        admin_debug('处理后数据', $this->updates);

        $this->newId = Admin::repository()->insert($this);

        admin_debug('保存结果: '.$this->newId);

        $this->callSaved();

        return $this->createResponseAfterInsert();
    }

    /**
     * 小驼峰字段转化为下划线风格字段
     *
     * @param array $data
     * @return array
     */
    protected function convertOriginalDbFields(array $data)
    {
        $new = [];
        foreach ($data as $k => &$v) {
            if (is_array($v)) {
                $v = $this->convertOriginalDbFields($v);
            }
            $new[str__slug($k, '_')] = $v;
        }
        return $new;
    }

    /**
     * Prepare input data for insert or update.
     *
     * @param array $data
     * @return void
     */
    protected function prepare($data = [])
    {
        $this->callSubmitted();

        $this->inputs = array_merge($this->removeIgnoredFields($data), $this->inputs);

        $this->callSaving();

        $this->updates = $this->inputs;
    }

    /**
     * Remove ignored fields from input.
     *
     * @param array $input
     *
     * @return array
     */
    protected function removeIgnoredFields($input)
    {
        array_forget($input, $this->ignored);

        return $input;
    }


    /**
     * Get ajax response.
     *
     * @param string $message
     *
     * @return bool|ResponseInterface
     */
    protected function ajaxResponse($message, bool $status = true)
    {
        // ajax but not pjax
        if (is_ajax_request() && !is_pjax_request()) {
            return response()->json([
                'status'  => $status,
                'message' => $message,
            ]);
        }

        return false;
    }

    /**
     * Call submitted callback.
     *
     * @return mixed
     */
    protected function callSubmitted()
    {
        foreach ($this->submitted as $func) {
            if ($func instanceof Closure) {
                call_user_func($func, $this);
            }
        }
    }

    /**
     * Call saving callback.
     *
     * @return mixed
     */
    protected function callSaving()
    {
        foreach ($this->saving as $func) {
            if ($func instanceof Closure) {
                call_user_func($func, $this);
            }
        }
    }

    /**
     * Callback after saving a Model.
     *
     * @return mixed|null
     */
    protected function callSaved()
    {
        foreach ($this->saved as $func) {
            if ($func instanceof Closure) {
                call_user_func($func, $this);
            }
        }
    }


    /**
     * 檢查文件上傳是否出錯
     *
     * @param array $input
     */
    protected function checkFileInput(array &$input)
    {
        foreach ($input as $k => $v) {
            if (is_array($v) && count($v) == 1) {
                $v = current($v);
            }
            if (!$v instanceof UploadedFile) {
                continue;
            }
            if (!$error = $v->getError()) {
                continue;
            }
            $message = t(static::$errors[$error], 'admin', [$v->getClientFilename()]);

            unset($input[$k]);
            if ($error === UPLOAD_ERR_NO_FILE) {
                admin_debug('没有上传文件 '.$message);
            } else {
                admin_error($message);

                admin_debug("检测到上传文件[$k]出错: {$message}", [], 'error');
            }
        }
    }

    /**
     * Check if request is from editable.
     *
     * @param array $input
     *
     * @return bool
     */
    protected function isEditable(array $input = [])
    {
        return array_key_exists('_editable', $input);
    }

    /**
     * 判断是否修改成功
     *
     * @return bool
     */
    public function updateSucceed()
    {
        return $this->updated;
    }

    /**
     * Handle update.
     *
     * @param mixed $id
     * @return Redirector
     */
    public function update($id = null)
    {
        $id = $id ?: $this->id;

        $this->id = $id;

        $data = $this->input->all();
        admin_debug('[更新]原始表单数据', $data);

        $this->checkFileInput($data);

        $isEditable = $this->isEditable($data);

        $data = $this->handleEditable($data);

        // Handle validation errors.
        if ($validationMessages = $this->validationMessages($data)) {
            if (!$isEditable) {
                flash_errors($validationMessages);
                flash_input($data);

                return new Redirector(false);
            }
            return (new Redirector(false))->final(response()->json(['errors' => array_dot($validationMessages)], 422));
        }

        $this->prepare($data);
        $this->setOriginal($id);

        $this->updates = $this->prepareUpdate($this->updates);
        admin_debug('处理后数据', $this->updates);

        $this->updated = $id ? Admin::repository()->update($this) : false;

        admin_debug('更新结果: '.$this->updated);

        $this->callSaved();

        return $this->redirectAfterUpdate($id);
    }

    protected function setOriginal($id)
    {
        $needOriginals = $this->getNeedOriginalValueFields();
        // 注入修改前數據
        if ($needOriginals) {
            $result = Admin::repository()->findForDeleteFiles($id);
            if ($result) {
                $this->setFieldOriginalValue($result);
            }
        }
    }

    /**
     * Destroy data entity and remove files.
     *
     * @param $id
     * @return bool
     */
    public function destroy($id = null)
    {
        $id = $id ?: $this->id;
        $this->id = $id;

        if (!$id) {
            return false;
        }

        collect(explode(',', $id))->filter()->each(function ($id) {
            $this->setOriginalAndDeleteFiles($id);
        });

        $result = Admin::repository()->delete($this);

        admin_debug("删除记录: $result");

        return (bool)$result;
    }

    /**
     * 删除(批量)记录
     *
     * @param mixed $id
     * @return ResponseInterface
     */
    public function destroyAndResponse($id = null)
    {
        if ($this->destroy($id)) {
            $data = [
                'status'  => true,
                'message' => t('Delete succeeded!', 'admin'),
            ];
        } else {
            $data = [
                'status'  => false,
                'message' => t('Delete failed!', 'admin'),
            ];
        }

        return response()->json($data);
    }

    /**
     * Remove files in record.
     */
    protected function setOriginalAndDeleteFiles($id)
    {
        if ($needOriginals = $this->getNeedOriginalValueFields()) {
            $files = Admin::repository()->findForDeleteFiles($id);
            if ($files) {
                $this->deleteFiles($files);
            }
        }
    }

    /**
     * 删除文件
     *
     * @param array|null $data 需要删除的字段数据
     */
    public function deleteFiles(array $data = null)
    {
        if ($data && $needOriginals = $this->getNeedOriginalValueFields()) {
            $this->setFieldOriginalValue($data);
            $this->deleteFiles();
        }
        $this->getFileFields()->each(function ($file) {
            if ($file instanceof Field\File) {
                $file->destroy();
            } elseif ($file instanceof Field\MultipleFile) {
                $file->destroyAll();
            }
        });
    }

    /**
     * 获取文件类型字段字段
     *
     * @return Collection
     */
    public function getFileFields()
    {
        return $this->builder->fields()->filter(function ($field) {
            return $field instanceof Field\File || $field instanceof Field\MultipleFile;
        });
    }

    /**
     * Get Redirect after store.
     *
     * @return Redirector
     */
    protected function createResponseAfterInsert()
    {
        $success = true;
        $msg     = t('Save succeeded!', 'admin');
        if (!$this->newId) {
            $msg     = t('Save failed!', 'admin');
            $success = false;
        }

        if ($response = $this->ajaxResponse($msg, $success)) {
            return (new Redirector($success))->final($response);
        }

        if ($this->newId) {
            admin_notice(t('Save succeeded!', 'admin'));
        } else {
            admin_notice(t('Save failed!', 'admin'), 'error');
        }

        return $this->redirectAfterSaving($this->newId, (bool)$this->newId);
    }

    /**
     * Get RedirectResponse after update.
     *
     * @param mixed $key
     * @return Redirector
     */
    protected function redirectAfterUpdate($key)
    {
        $msg = $this->updated ? t('Update succeeded!', 'admin') : t('Nothing has been changed!', 'admin');

        if ($response = $this->ajaxResponse($msg)) {
            return (new Redirector(true))->final($response);
        }

        admin_notice($msg);

        return $this->redirectAfterSaving($key, true);
    }

    /**
     * Get RedirectResponse after data saving.
     *
     * @param string $id
     * @param bool $success
     * @return Redirector
     */
    protected function redirectAfterSaving($id, bool $success)
    {
        if ($success && $this->input->request('after-save') == 1) {
            return (new Redirector($success))->final(Admin::url()->edit($id));

        }
        if ($success && $this->input->request('after-save') == 2) {
            return (new Redirector($success))->final(Admin::url()->view($id));
        }

        $url = $this->input->request(Builder::PREVIOUS_URL_KEY) ?: Url::previous();

        return new Redirector($success, $url);
    }

    /**
     * Handle editable update.
     *
     * @param array $input
     *
     * @return array
     */
    protected function handleEditable(array $input = [])
    {
        if (array_key_exists('_editable', $input)) {
            $name = $input['name'];
            $value = $input['value'];

            array_forget($input, ['pk', 'value', 'name']);
            array_set($input, $name, $value);
        }

        return $input;
    }

    /**
     * Prepare input data for update.
     *
     * @param array $updates
     * @return array
     */
    protected function prepareUpdate(array &$updates)
    {
        $prepared = [];

        /** @var Field $field */
        foreach ($this->builder->fields() as $field) {
            $columns = $field->column();

            // If column not in input array data, then continue.
            if (!array_has($updates, $columns)) {
                continue;
            }

            $value = $this->getDataByColumn($updates, $columns);

            $value = $field->prepare($value);
            if ($value === null) {
                continue;
            }

            if (is_array($columns)) {
                foreach ($columns as $name => $column) {
                    array_set($prepared, $column, $value[$name]);
                }
            } elseif (is_string($columns)) {
                array_set($prepared, $columns, $value);
            }
        }

        return $prepared;
    }

    /**
     * Prepare input data for insert.
     *
     * @param array $inserts
     * @return array
     */
    protected function prepareInsert(&$inserts)
    {
        foreach ($inserts as $column => $value) {
            if (is_null($field = $this->getFieldByColumn($column))) {
                unset($inserts[$column]);
                continue;
            }

            $value = $field->prepare($value);
            if ($value === null) {
                continue;
            }

            $inserts[$column] = $value;
        }

        return $inserts;
    }

    /**
     * Get validation messages.
     *
     * @param array $input
     * @return string|array|null
     */
    protected function validationMessages(array &$input)
    {
        $rules = $messages = $attributes = [];

        /** @var Field $field */
        foreach ($this->builder->fields() as $field) {
            if (!$validators = $field->getValidatorData($input)) {
                continue;
            }

            $rules      = array_merge($rules, $validators[0]);
            $messages   = array_merge($messages, $validators[1]);
            $attributes = array_merge($attributes, $validators[1]);
        }

        $validator = Validator::make($rules, $messages, $attributes);
        $validator->batch(true);

        return $validator->check($input) ? null : $validator->getError();

    }

}
