<?php

namespace Swoft\Admin\Grid\Displayers;

use Swoft\Admin\Admin;
use Swoft\Admin\Widgets\Dump;
use Swoft\Support\Contracts\Renderable;

class Expand extends AbstractDisplayer
{
    /**
     * 显示内容
     *
     * @var string|Renderable|\Closure
     */
    protected $content;

    /**
     * 按钮名称
     *
     * @var string
     */
    protected $label;

    /**
     * @var bool
     */
    protected $useDump = false;

    /**
     * ajax请求地址
     *
     * @var string
     */
    protected $ajax;

    /**
     * post数据
     *
     * @var array
     */
    protected $post;

    /**
     * 发送ajax请求获取内容
     *
     * @param string $api
     * @param array  $post
     * @return $this
     */
    public function url(string $api, array $post = [])
    {
        $this->ajax = &$api;
        $this->post = &$post;
        $this->content = ' ';
        return $this;
    }

    /**
     * 设置显示内容
     *
     * @param string|\Closure|Renderable $content
     * @return $this
     */
    public function content($content)
    {
        if ($content instanceof \Closure) {
            $this->content = $content($this->row);
        } elseif ($content instanceof Renderable) {
            $this->content = $content->render();
        } else {
            $this->content = $content;
        }

        return $this;
    }

    /**
     * 设置label
     *
     * @param string $label
     * @return $this
     */
    public function label(string $label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * 使用Dump组件渲染内容
     *
     * @return $this
     */
    public function dump()
    {
        $this->useDump = true;
        return $this;
    }

    public function display($label = '', $dump = null)
    {
        $id = 'e'.uniqid();

        // 初始化js
        $this->setupScript($id);

        $this->content = $this->content ?: $this->value;
        $this->label   = $this->label ?: t('Detail', 'admin');
        $this->useDump = $dump === null ? $this->useDump : $dump;

        $color  = 'default';
        $action = '';
        if (!$this->ajax) {
            $action = 'data-toggle="collapse"';
        }

        if ($this->useDump) {
            if (is_string($this->content)) {
                if (is_array($decode = json_decode($this->content, true))) {
                    $this->content = &$decode;
                }
            }
            if (is_array($this->content)) {
                $this->content = (new Dump($this->content))->render();
            } else {
                // 文本需要自动换行
                $this->content = (new Dump($this->content))->autoWrap()->render();
            }
        }

        $this->grid->addExpandRow(
            "<div id=\"$id\" class=\"panel-collapse collapse\">{$this->content}</div>"
        );

        return
            "<a class=\"btn btn-xs btn-$color grid-expand\" $action data-target=\"#$id\"><i class=\"fa fa-caret-right\"></i> {$this->label}</a>";

    }

    protected function setupScript($id)
    {
        $script = '';

        $post = json_encode($this->post);

        if ($this->ajax) {
            $script = <<<EOF
var targ = t.data('target'), \$c =$(targ);t.button('loading');t.attr('ajax',1);NProgress.start();
$.post('$this->ajax', window['expandpost'+targ.replace('#', '')], function(d){
    NProgress.done();
    if (d) 
       \$c.html(d);
    else 
       \$c.html('{$this->noDataTip()}');
       
       id.collapse('show');
    setTimeout(function(){
        t.button('reset');
    },200)
});
EOF;
            // 保存post数据
            Admin::script("window['expandpost{$id}']=$post;");
        }

        Admin::script(<<<SCRIPT
$('.grid-expand').click(function(){
    var t = $(this),
        i = t.find('i'),
        id = $(t.data('target'));
    i.toggleClass('fa-caret-right');
    i.toggleClass('fa-caret-down');
    if (t.attr('ajax')) {
        id.collapse('toggle');
        return; 
    }
    {$script}
});        
SCRIPT
        );
    }

    /**
     * @return string
     */
    protected function noDataTip()
    {
        $tip = t('No Data.');
        return <<<EOF
<table class="table"><tr><td><div style="margin:5px 0 0 15px;"><span class="help-block" style="margin-bottom:0"><i class="fa fa-info-circle"></i>&nbsp;{$tip}</span></div></td></tr></table>
EOF;

    }

}
