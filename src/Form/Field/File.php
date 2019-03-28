<?php

namespace Swoft\Admin\Form\Field;

use Swoft\Admin\Form\Field;
use Swoft\Support\Input;
use Swoft\Http\Message\Upload\UploadedFile;

class File extends Field
{
    use UploadField;

    /**
     * Css.
     *
     * @var array
     */
    protected static $css = [
        '@admin/bootstrap-fileinput/css/fileinput.min.css',
    ];

    /**
     * Js.
     *
     * @var array
     */
    protected static $js = [
        '@admin/bootstrap-fileinput/js/plugins/canvas-to-blob.min.js',
        '@admin/bootstrap-fileinput/js/fileinput.min.js',
    ];

    protected $needOriginal = true;

    /**
     * Create a new File instance.
     *
     * @param string $column
     * @param array  $arguments
     */
    public function __construct($column, $arguments = [])
    {
        $this->initStorage();

        parent::__construct($column, $arguments);
    }

    /**
     * Default directory for file to upload.
     *
     * @return mixed
     */
    public function defaultDirectory()
    {
        return config('admin.upload.directory.file');
    }

    /**
     * {@inheritdoc}
     */
    public function getValidatorData(array &$input)
    {
        if (Input::request($this->column) === static::FILE_DELETE_FLAG) {
            return false;
        }

        /*
         * If has original value, means the form is in edit mode,
         * then remove required rule from rules.
         */
        if ($this->original()) {
            $this->removeRule('required');
        }

        /*
         * Make input data validatable if the column data is `null`.
         */
        if (array_has($input, $this->column) && is_null(array_get($input, $this->column))) {
            $input[$this->column] = '';
        }

        $rules = $attributes = [];

        if (!$fieldRules = $this->getRules()) {
            return false;
        }

        $rules[$this->column] = $fieldRules;
        $attributes[$this->column] = $this->label;

        return [$rules, $this->validationMessages, $attributes];
    }

    /**
     * Prepare for saving.
     *
     * @param UploadedFile|array $file
     *
     * @return string
     */
    public function prepare($file)
    {
        if (Input::request($this->column) === static::FILE_DELETE_FLAG) {
            $this->destroy();
            return '';
        }

        $this->name = $this->getStoreName($file);

        return $this->uploadAndDeleteOriginal($file);
    }

    /**
     * Upload file and delete original file.
     *
     * @param UploadedFile $file
     *
     * @return mixed
     */
    protected function uploadAndDeleteOriginal(UploadedFile $file)
    {
        $this->renameIfExists($file);

        $filename = $this->getDirectory().'/'.$this->name;

        if (!$this->storage->put($filename, $this->getContent($file))) {
            admin_debug('文件上传失败！', [], 'error');
            // 上传失败返回null，则不会修改数据
            return null;
        }

        $this->form->saved(function () {
            if ($this->form->updateSucceed()) {
                $this->destroy();
            }
        });

        return $filename;
    }

    /**
     * Preview html for file-upload plugin.
     *
     * @return string
     */
    protected function preview()
    {
        return $this->objectUrl($this->value);
    }

    /**
     * Initialize the caption.
     *
     * @param string $caption
     *
     * @return string
     */
    protected function initialCaption($caption)
    {
        return basename($caption);
    }

    /**
     * @return array
     */
    protected function initialPreviewConfig()
    {
        return [
            ['caption' => basename($this->value), 'key' => 0],
        ];
    }

    /**
     * Render file upload field.
     *
     * @return string
     */
    public function render()
    {
        $this->options(['overwriteInitial' => true]);
        $this->setupDefaultOptions();

        if (!empty($this->value)) {
            $this->attribute('data-initial-preview', $this->preview());
            $this->attribute('data-initial-caption', $this->initialCaption($this->value));

            $this->setupPreviewOptions();
        }

        $options = json_encode($this->options);

        $this->script = <<<EOT
$("input{$this->getElementClassSelector()}").fileinput({$options});
EOT;

        return parent::render();
    }
}
