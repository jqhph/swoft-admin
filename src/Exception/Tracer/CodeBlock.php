<?php

namespace Swoft\Admin\Exception\Tracer;

class CodeBlock
{
    protected $line = '';

    protected $suffix = '';

    protected $prefix = '';

    protected $startLine = 0;

    public function __construct($startLine = 0, $line = '', $prefix = '', $suffix = '')
    {
        $this->startLine = $startLine;
        $this->line = $line;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }

    public function getStartLine()
    {
        return $this->startLine;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function output()
    {
        return htmlentities($this->prefix.$this->line.$this->suffix);
    }
}
