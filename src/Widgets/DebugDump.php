<?php

namespace Swoft\Admin\Widgets;

use Swoft\Support\Contracts\Renderable;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;

class DebugDump implements Renderable
{
    /**
     * @var mixed
     */
    protected $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function render()
    {
        VarDumper::setHandler(function ($data) {
            $cloner = new VarCloner();
            $dumper = new HtmlDumper();
            $dumper->dump($cloner->cloneVar($data));
        });

        ob_start();
        VarDumper::dump($this->content);

        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

}
