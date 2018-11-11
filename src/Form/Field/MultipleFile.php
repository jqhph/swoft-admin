<?php

namespace Swoft\Admin\Form\Field;

use Swoft\Admin\Form\Field;
use Swoft\Support\Input;
use Swoft\Http\Message\Upload\UploadedFile;

class MultipleFile extends Field
{
    use UploadField;

    /**
     * @var bool
     */
    protected $needOriginal = true;

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
    public function getValidatorData(array $input)
    {
        if (Input::request($this->column) === static::FILE_DELETE_FLAG) {
            return false;
        }

        $attributes = [];

        if (!$fieldRules = $this->getRules()) {
            return false;
        }

        $attributes[$this->column] = $this->label;

        list($rules, $input) = $this->hydrateFiles(array_get($input, $this->column, []));

        return [$rules, $this->validationMessages, $attributes];
    }

    /**
     * Hydrate the files array.
     *
     * @param array $value
     *
     * @return array
     */
    protected function hydrateFiles(array $value)
    {
        if (empty($value)) {
            return [[$this->column => $this->getRules()], []];
        }

        $rules = $input = [];

        foreach ($value as $key => $file) {
            $rules[$this->column.$key] = $this->getRules();
            $input[$this->column.$key] = $file;
        }

        return [$rules, $input];
    }

    /**
     * Prepare for saving.
     *
     * @param UploadedFile|array $files
     *
     * @return mixed|string
     */
    public function prepare($files)
    {
        if (Input::request($this->column) === static::FILE_DELETE_FLAG) {
            return $this->destroy(Input::request('key'));
        }

        $targets = array_map([$this, 'prepareForeach'], $files);

        return implode(',', array_merge($this->original(), $targets));
    }

    /**
     * @return array|mixed
     */
    public function original()
    {
        if (empty($this->original)) {
            return [];
        }

        return explode(',', $this->original);
    }

    /**
     * Prepare for each file.
     *
     * @param UploadedFile $file
     *
     * @return mixed|string
     */
    protected function prepareForeach(UploadedFile $file = null)
    {
        $this->name = $this->getStoreName($file);

        return tap($this->upload($file), function () {
            $this->name = null;
        });
    }

    /**
     * Preview html for file-upload plugin.
     *
     * @return array
     */
    protected function preview()
    {
        return array_map([$this, 'objectUrl'], explode(',', $this->value));
    }

    /**
     * Initialize the caption.
     *
     * @param array $caption
     *
     * @return string
     */
    protected function initialCaption($caption)
    {
        if (empty($caption)) {
            return '';
        }

        $caption = array_map('basename', $caption);

        return implode(',', $caption);
    }

    /**
     * @param int $max
     * @return $this
     */
    public function max(int $max)
    {
        $this->options['maxFileCount'] = $max;
        return $this;
    }

    /**
     * @return array
     */
    protected function initialPreviewConfig()
    {
        $files = explode(',', $this->value) ?: [];

        $config = [];

        foreach ($files as $index => $file) {
            $config[] = [
                'caption' => basename($file),
                'key'     => $index,
            ];
        }

        return $config;
    }

    /**
     * Render file upload field.
     *
     * @return string
     */
    public function render()
    {
        $this->attribute('multiple', true);

        $this->setupDefaultOptions();

        if (!empty($this->value)) {
            $this->options(['initialPreview' => $this->preview()]);
            $this->setupPreviewOptions();
        }
        $this->options['autoReplace'] = false;

        $options = json_encode($this->options);

        $this->script = <<<EOT
$("input{$this->getElementClassSelector()}").fileinput({$options});
EOT;

        return parent::render();
    }

    /**
     * Destroy original files.
     *
     * @param int $key
     * @return array
     */
    public function destroy($key)
    {
        $files = $this->original ? explode(',', $this->original) : [];

        $file = array_get($files, $key);

        if ($this->storage->has($file)) {
            if ($this->storage->delete($file)) {
                admin_debug("[{$this->label}]移除文件: $file");
            }
        }

        unset($files[$key]);

        return implode(',', array_values($files));
    }

    /**
     * 删除所有文件
     */
    public function destroyAll()
    {
        $files = $this->original ? explode(',', $this->original) : [];

        foreach ($files as &$file) {
            if ($this->storage->has($file)) {
                $this->storage->delete($file);
                admin_debug("[{$this->label}]移除文件: $file");
            }
        }
    }
}
