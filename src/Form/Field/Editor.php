<?php

namespace Swoft\Admin\Form\Field;

use Swoft\Admin\Form\Field;

/**
 * @see https://pandao.github.io/editor.md/
 */
class Editor extends Field
{
    protected static $css = [
        '@admin/editor-md/css/editormd.min.css'
    ];

    protected static $js = [
        '@admin/editor-md/lib/raphael.min.js',
        '@admin/editor-md/editormd.min.js',
        '@admin/editor-md/languages/en.js'
    ];

    /**
     * 编辑器配置
     *
     * @var array
     */
    protected $options = [
        'height'             => 600,
        'codeFold'           => true,
        'saveHTMLToTextarea' => true, // 保存 HTML 到 Textarea
        'searchReplace'      => true,
//        'htmlDecode' => 'style,script,iframe|on*', // 开启 HTML 标签解析，为了安全性，默认不开启
        'emoji'              => true,
        'taskList'           => true,
        'tocm'               => true,         // Using [TOCM]
        'tex'                => true,                   // 开启科学公式TeX语言支持，默认关闭
        'flowChart'          => true,             // 开启流程图支持，默认关闭
        'sequenceDiagram'    => true,       // 开启时序/序列图支持，默认关闭,
        'imageUpload'        => true,
        'imageFormats'       => ['jpg', 'jpeg', 'gif', 'png', 'bmp', 'webp'],
        'imageUploadURL'     => '',
        'autoFocus'          => false,
    ];

    /**
     * 开启 HTML 标签解析，为了安全性，默认不开启
     * style,script,iframe|on*
     *
     * @param string $decode
     * @return $this
     */
    public function htmlDecode($decode)
    {
        $this->options['htmlDecode'] = &$decode;

        return $this;
    }

    /**
     * 设置编辑器容器高度
     *
     * @param int $height
     * @return $this
     */
    public function height($height)
    {
        $this->options['height'] = $height;

        return $this;
    }

    /**
     * 初始化js
     */
    protected function setupScript()
    {
        $id = 'e'.uniqid();

        $this->attribute('id', $id);

        $this->options['path'] = admin_asset('editor-md/lib/');
        $this->options['name'] = $this->column;
        $this->options['placeholder'] = $this->getPlaceholder();

        $opts = json_encode($this->options);

        $this->script = "editormd(\"{$id}\", $opts);";
    }


    public function render()
    {
        $this->setupScript();
        return parent::render();
    }
}
