<?php

namespace Swoft\Admin\Exception\Tracer;

class Parser implements \Iterator
{
    protected $trace = '';

    protected $frames = [];

    public function __construct($trace)
    {
        $this->trace = $trace;
    }

    public function parse()
    {
        $frames = explode("\n", $this->trace);
        $this->frames = array_map(function ($frame) {
            return new Frame($frame);
        }, $frames);

        return $this->frames;
    }

    public function current()
    {
    }

    public function next()
    {
    }

    public function key()
    {
    }

    public function valid()
    {
    }

    public function rewind()
    {
    }
}
