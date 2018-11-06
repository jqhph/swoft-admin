<?php

namespace Swoft\Admin\Widgets;

use Swoft\Admin\Admin;
use Swoft\Support\Contracts\Renderable;

class Markdown extends Widget
{
    /**
     * @var string
     */
    protected $content;

    /**
     * 配置
     *
     * @var array
     */
    protected $options = [
        'htmlDecode' => 'style,script,iframe',
        'emoji' => true,
        'taskList' => true,
        'tex' => true,
        'flowChart' => true,
        'sequenceDiagram' => true,
    ];

    public function __construct($markdown = '')
    {
        $markdown && $this->content($markdown);

        Admin::css([
            '@admin/editor-md/css/editormd.preview.min.css',
            '@admin/swoft-admin/markdown.css'
        ]);

        Admin::js([
            '@admin/editor-md/lib/raphael.min.js',
            '@admin/editor-md/lib/marked.min.js',
            '@admin/editor-md/lib/prettify.min.js',
            '@admin/editor-md/lib/underscore.min.js',
            '@admin/editor-md/lib/sequence-diagram.min.js',
            '@admin/editor-md/lib/flowchart.min.js',
            '@admin/editor-md/lib/jquery.flowchart.min.js',
            '@admin/editor-md/editormd.min.js'
        ]);
    }

    /**
     * @param mixed $k
     * @param mixed $v
     * @return $this
     */
    public function option($k, $v)
    {
        $this->options[$k] = $v;

        return $this;
    }

    /**
     *
     * @param string|Renderable $markdown
     * @return $this
     */
    public function content($markdown)
    {
        $this->content = &$markdown;
        return $this;
    }

    protected function build()
    {
        if ($this->content instanceof Renderable) {
            $this->content = $this->content->render();
        }

        return <<<EOF
<div {$this->formatAttributes()}><textarea style="display:none;">{$this->content}</textarea></div>
EOF;

    }

    public function render()
    {
        $id = uniqid();

        $this->defaultAttribute('id', $id);

        $opts = json_encode($this->options);

        Admin::script("editormd.markdownToHTML('$id', $opts);");

        return $this->build();
    }

}
