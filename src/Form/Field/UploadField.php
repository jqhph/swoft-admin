<?php

namespace Swoft\Admin\Form\Field;

use Swoft\Admin\Admin;
use Swoft\Admin\Form;
use Swoft\Http\Message\Upload\UploadedFile;
use Swoft\Support\SessionHelper;
use League\Flysystem\Filesystem;

trait UploadField
{
    /**
     * Upload directory.
     *
     * @var string
     */
    protected $directory = '';

    /**
     * File name.
     *
     * @var null
     */
    protected $name = null;

    /**
     * Storage instance.
     *
     * @var Filesystem
     */
    protected $storage = '';

    /**
     * If use unique name to store upload file.
     *
     * @var bool
     */
    protected $useUniqueName = false;

    /**
     * @var bool
     */
    protected $removable = true;

    /**
     * @var string
     */
    protected $disk = 'local';

    /**
     * Initialize the storage instance.
     *
     * @return void.
     */
    protected function initStorage()
    {
        $this->disk($this->disk);
    }

    /**
     * Set default options form image field.
     *
     * @return void
     */
    protected function setupDefaultOptions()
    {
        $session = SessionHelper::wrap();
        if (empty($this->options['initialPreviewFileType'])) {
            $this->setPreviewType('text');
        }

        $defaultOptions = [
            'overwriteInitial'     => false,
            'initialPreviewAsData' => true,
            'browseLabel'          => t('Browse', 'admin'),
            'showRemove'           => false,
            'showUpload'           => false,
            'showPreview'          => true,
//            'initialPreviewShowDelete'          => true,
            'deleteExtraData'      => [
                $this->formatName($this->column) => static::FILE_DELETE_FLAG,
                '_token'                         => $session ? $session->token() : '',
                '_method'                        => 'PUT',
            ],
        ];

        if ($this->form instanceof Form && $this->form->isEditMode()) {
            $defaultOptions['deleteUrl'] = Admin::url()->updateField($this->form->getId());
        }

        $this->options($defaultOptions);
    }

    /**
     * Set preview options form image field.
     *
     * @return void
     */
    protected function setupPreviewOptions()
    {
        if (!$this->removable) {
            return;
        }

        $this->options([
//            'initialPreview'        => $this->preview(),
            'initialPreviewConfig' => $this->initialPreviewConfig(),
        ]);
    }

    /**
     * 预览下载链接，如
     * http://kartik-v.github.io/bootstrap-fileinput-samples/samples/{filename}
     *
     * @param string $url
     * @return $this
     */
    public function setPreviewDownloadUrl(string $url)
    {
        return $this->options(['initialPreviewDownloadUrl' => $url]);
    }

    /**
     * 设置预览类型
     * image, text, html, video, office, gdocs, pdf
     *
     * @param string $type
     * @return $this
     */
    public function setPreviewType(string $type)
    {
        return $this->options(['initialPreviewFileType' => $type]);
    }

    /**
     * Allow use to remove file.
     *
     * @return $this
     */
    public function removable()
    {
        $this->removable = true;

        return $this;
    }

    /**
     * Disable use to remove file.
     *
     * @return $this
     */
    public function disableRemove()
    {
        $this->removable = false;
        return $this;
    }

    /**
     * Set options for file-upload plugin.
     *
     * @param array $options
     *
     * @return $this
     */
    public function options($options = [])
    {
        $this->options = array_merge($options, $this->options);

        return $this;
    }

    /**
     * Set disk for storage.
     *
     * @param string $disk Disks defined in `config/filesystems.php`.
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function disk($disk)
    {
        try {
            $config        = config('admin.upload.filesystem.'.($disk ?: 'local'));
            $adapterConfig = (array)array_get($config, 'adapter');

            $adapter = array_get($adapterConfig, 'class');

            $adapter = new $adapter(...(array)array_get($adapterConfig, 0));

            $this->storage = new Filesystem($adapter, $config);
        } catch (\Exception $exception) {
            throw $exception;
        }

        return $this;
    }

    /**
     * Specify the directory and name for upload file.
     *
     * @param string      $directory
     * @param null|string $name
     *
     * @return $this
     */
    public function move($directory, $name = null)
    {
        $this->dir($directory);

        $this->name($name);

        return $this;
    }

    /**
     * Specify the directory upload file.
     *
     * @param string $dir
     *
     * @return $this
     */
    public function dir($dir)
    {
        if ($dir) {
            $this->directory = $dir;
        }

        return $this;
    }

    /**
     * Set name of store name.
     *
     * @param string|callable $name
     *
     * @return $this
     */
    public function name($name)
    {
        if ($name) {
            $this->name = $name;
        }

        return $this;
    }

    /**
     * Use unique name for store upload file.
     *
     * @return $this
     */
    public function uniqueName()
    {
        $this->useUniqueName = true;

        return $this;
    }

    /**
     * Get store name of upload file.
     *
     * @param UploadedFile $file
     *
     * @return string
     */
    protected function getStoreName(UploadedFile $file)
    {
        if ($this->useUniqueName) {
            return $this->generateUniqueName($file);
        }

        if ($this->name instanceof \Closure) {
            return $this->name->call($this, $file);
        }

        if (is_string($this->name)) {
            return $this->name;
        }

        return $this->normalizeFilename($file);
    }

    /**
     * @param UploadedFile $file
     * @param bool $uniqid
     * @return string
     */
    protected function normalizeFilename(UploadedFile $file, bool $uniqid = false)
    {
        $filename = $file->getClientFilename();
        $ext      = array_get(pathinfo($filename), 'extension');

        return md5(($uniqid ? uniqid() : '') . $filename) . ($ext ? ".$ext" : '');
    }

    /**
     * Get directory for store file.
     *
     * @return mixed|string
     */
    public function getDirectory()
    {
        if ($this->directory instanceof \Closure) {
            return call_user_func($this->directory, $this->form);
        }

        return $this->directory ?: $this->defaultDirectory();
    }

    /**
     * Upload file and delete original file.
     *
     * @param UploadedFile $file
     *
     * @return mixed
     */
    protected function upload(UploadedFile $file)
    {
        if (!$this->name) {
            $this->name = $this->getStoreName($file);
        }

        $this->renameIfExists($file);

        $filename = $this->getDirectory().'/'.$this->name;
        if (!$this->storage->put($filename, $this->getContent($file))) {
            admin_debug("[{$this->label}]检测到文件上传失败：{$filename}", [], 'error');
        }
        return $filename;
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    protected function getContent(UploadedFile $file)
    {
        $tmp = $this->getTmpPath($file);
        if (!is_file($tmp)) {
            admin_debug("[{$this->label}]上传文件检测到临时文件不存在：{$tmp}", [], 'error');
            return '';
        }
        return filesystem()->get($tmp);
    }

    /**
     * 获取临时文件路径
     *
     * @param UploadedFile $file
     * @return mixed
     */
    protected function getTmpPath(UploadedFile $file)
    {
        return array_get($file->toArray(), 'tmp_file');
    }

    /**
     * If name already exists, rename it.
     *
     * @param $file
     *
     * @return void
     */
    public function renameIfExists(UploadedFile $file)
    {
        if ($this->storage->has("{$this->getDirectory()}/{$this->name}")) {
            $this->name = $this->generateUniqueName($file);
        }
    }

    /**
     * Get file visit url.
     *
     * @param $path
     *
     * @return string
     */
    public function objectUrl($path)
    {
        if (is_valid_url($path)) {
            return $path;
        }

        if (!$this->storage) {
            $this->initStorage();
        }

        if (!$url = $this->storage->getConfig()->get('url')) {
            return $path;
        }

        return \Swoft\Support\Url::to($url.'/'.trim($path, '/'));
    }

    /**
     * Generate a unique name for uploaded file.
     *
     * @param UploadedFile $file
     *
     * @return string
     */
    protected function generateUniqueName(UploadedFile $file)
    {
        return $this->normalizeFilename($file, true);
    }

    /**
     * Destroy original files.
     *
     * @param string|null $path
     */
    public function destroy(string $path = null)
    {
        if (!$path = $path ?: $this->original) {
            return;
        }
        admin_debug("[{$this->label}]文件存在则删除: $path");
        if ($this->storage->has($path)) {
            $this->storage->delete($path);
            admin_debug("[{$this->label}]移除文件: $path");
        }
    }
}
