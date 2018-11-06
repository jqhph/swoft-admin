<?php

namespace Swoft\Admin\Form\Field;

use Intervention\Image\ImageManagerStatic;
use Swoft\Http\Message\Upload\UploadedFile;

trait ImageField
{
    /**
     * Intervention calls.
     *
     * @var array
     */
    protected $interventionCalls = [];

    /**
     * Default directory for file to upload.
     *
     * @return mixed
     */
    public function defaultDirectory()
    {
        return config('admin.upload.directory.image');
    }

    /**
     * Execute Intervention calls.
     *
     * @param UploadedFile $image
     *
     * @return mixed
     */
    public function callInterventionMethods(UploadedFile $image)
    {
        $path = $this->getTmpPath($image);
         if (!is_file($path)) {
             admin_debug("上传图片检测到临时文件不存在：{$path}", [], 'error');
             return '';
         }

        if (!empty($this->interventionCalls)) {
            $image = ImageManagerStatic::make($path);

            foreach ($this->interventionCalls as $call) {
                call_user_func_array(
                    [$image, $call['method']],
                    $call['arguments']
                )->save($path);
            }
        }
    }

    /**
     * Call intervention methods.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function __call($method, $arguments)
    {
        if (!class_exists(ImageManagerStatic::class)) {
            throw new \Exception('To use image handling and manipulation, please install [intervention/image] first.');
        }

        $this->interventionCalls[] = [
            'method'    => $method,
            'arguments' => $arguments,
        ];

        return $this;
    }

}
