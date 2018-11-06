<?php

namespace Swoft\Admin\Grid\Displayers;

use Swoft\Admin\Admin;

class SwitchDisplay extends AbstractDisplayer
{
    /**
     * @var string
     */
    protected $url;

    /**
     * 设置修改字段的url
     *
     * @param string $url
     * @return $this
     */
    public function url(string $url)
    {
        $this->url = rtrim($url, '/');
        return $this;
    }

    public function primary()
    {
        return $this->attribute('data-color', '#0072C6');
    }

    public function green()
    {
        return $this->attribute('data-color', '#00b19d');
    }

    public function info()
    {
        return $this->attribute('data-color', '#3bafda');
    }

    public function warning()
    {
        return $this->attribute('data-color', '#ffaa00');
    }

    public function inverse()
    {
        return $this->attribute('data-color', '#4c5667');
    }

    public function danger()
    {
        return $this->attribute('data-color', '#ef5350');
    }

    public function purple()
    {
        return $this->attribute('data-color', '#5b69bc');
    }

    /**
     *
     * @param $color
     * @return $this
     */
    public function secondary($color)
    {
        return $this->attribute('data-secondary-color', $color);
    }

    /**
     * 设置小尺寸
     *
     * @return $this
     */
    public function small()
    {
        return $this->attribute('data-size', 'small');
    }

    /**
     * 大尺寸
     *
     * @return $this
     */
    public function large()
    {
        return $this->attribute('data-size', 'large');
    }

    /**
     * 禁选
     *
     * @return $this
     */
    public function disabled()
    {
        return $this->attribute('disabled', 'disabled');
    }

    /**
     * 设置颜色
     *
     * @param $color
     * @return $this
     */
    public function color($color)
    {
        return $this->attribute('data-color', $color);
    }

    public function display(string $url = null, string $color = '')
    {
        if ($url) {
            $this->url($url);
        }
        if ($color) {
            $this->color($color);
        }
        $this->setupAssets();

        if (empty($this->attributes['data-size'])) {
            $this->small();
        }
        if (empty($this->attributes['data-color'])) {
            $this->primary();
        }

        $name    = $this->column->getName();
        $key     = $this->row->{$this->grid->getKeyName()};
        $checked = $this->value ? 'checked' : '';

        $this->class("grid-switch-{$name}");

        return <<<EOF
<input name="{$name}" data-key="$key" $checked type="checkbox" data-switchery="1" {$this->formatAttributes()}/>
EOF;
    }

    protected function setupAssets()
    {
        Admin::css([
            '@admin/switchery/switchery.min.css'
        ]);
        Admin::js([
            '@admin/switchery/switchery.min.js'
        ]);

        $url = $this->url ?: (Admin::url()->updateField());

        Admin::script(<<<EOF
(function(){
var doms = $('[data-switchery="1"]'), r = 0, list = {}, url = '$url';
function init(){
    doms.each(function(k){
         var _t = $(this);
        list[k] = new Switchery(_t[0], _t.data())
    })
} 
init();
doms.change(function(e) {
    if (r) return;
    var t = $(this); id=t.data('key'),all=$('.switchery'),checked=t.is(':checked'), name = t.attr('name');
    var data = {
        name: name,
        _token: LA.token,
        _editable: 1
    };
    data['value'] = checked ? 1 : 0;

    $.ajax({
        url: url.replace(':id', id),
        type: "POST",
        data: data,
        success: function (data) {
             if (data.errors) {
                return LA.error(data.errors.join("\\n"));
            }
            if (data.status) {
                LA.success(data.message);
            } else {
                LA.error(data.message);
            }
        }
    });
});
})();
EOF
        );
    }

}
