<?php

namespace Swoft\Admin\Exception\Tracer;

use Swoft\Support\Str;

class Frame
{
    protected $frame = '';

    protected $attributes = [];

    protected $code = [];

    public function __construct($frame = '')
    {
        $this->frame = $frame;
        $this->extract();
    }

    public function extract()
    {
        preg_match('/#\d+\s([^:]+):?\s?(.*)/', $this->frame, $matches);
        if (!empty($matches[1])) {
            $this->parseFileAndLine($matches[1]);
        }
        if (!empty($matches[2])) {
            $this->parseCall($matches[2]);
        }
        $this->fetchCodeBlock();

        return $this->attributes;
    }

    public function parseFileAndLine($str)
    {
        if (Str::startsWith($str, '/')) {
            preg_match('/^([^(]+)\((\d+)\)/', $str, $matches);
            list(, $this->attributes['file'], $this->attributes['line']) = $matches;
        } else {
            $this->attributes['name'] = $str;
        }
    }

    public function parseCall($str)
    {
        if (empty($str)) {
            return;
        }
        if (preg_match('/^[^(]+(->|::)/', $str, $m)) {
            preg_match('/([^:-]+)(?:->|::)([^(]+)\((.*)\)/', $str, $matches);
            $this->attributes['class'] = $matches[1];
            $this->attributes['method'] = $matches[2];
            $this->attributes['args'] = $this->extractArgs($matches[3]);
            if (Str::contains($matches[2], ['{closure}']) && array_get($this->attributes, 'name') == '[internal function]') {
                $this->attributes['name'] .= " $matches[1]->$matches[2]";
            }
            // class method call
        } else {
            preg_match('/([^(]+)\((.*)\)/', $str, $matches);
            $this->attributes['function'] = $matches[1];
            $this->attributes['args'] = $this->extractArgs($matches[2]);
        }
    }

    public function fetchCodeBlock()
    {
        $filename = array_get($this->attributes, 'file');
        $lineNo = array_get($this->attributes, 'line');
        $class = array_get($this->attributes, 'class');
        $method = array_get($this->attributes, 'method');
        if ((!$filename || !$lineNo) && ($class && $method)) {
            if (!class_exists($class)) {
                return;
            }
            $classReflection = new \ReflectionClass($class);
            $filename = $classReflection->getFileName();
            if (!$classReflection->hasMethod($method)) {
                return;
            }
            $methodReflection = $classReflection->getMethod($method);

            $lineNo = $methodReflection->getStartLine();
        }
        if (!$filename || !$lineNo) {
            return;
        }

        try {
            $file = new \SplFileObject($filename);
            $target = max(0, ($lineNo - (15 + 1)));
            $file->seek($target);
            $curLineNo = $target + 1;
            $line = $prefix = $suffix = '';
            while (!$file->eof()) {
                if ($curLineNo == $lineNo) {
                    $line .= $file->current();
                } elseif ($curLineNo < $lineNo) {
                    $prefix .= $file->current();
                } elseif ($curLineNo > $lineNo) {
                    $suffix .= $file->current();
                }
                $curLineNo++;
                if ($curLineNo > $lineNo + 15) {
                    break;
                }
                $file->next();
            }
            $this->code = new CodeBlock($target + 1, $line, $prefix, $suffix);
            $this->attributes['file'] = $filename;
            $this->attributes['line'] = $lineNo;
        } catch (\RuntimeException $exc) {
            return;
        }
    }

    public function getCodeBlock()
    {
        if (empty($this->code)) {
            return new CodeBlock();
        }

        return $this->code ?: new CodeBlock();
    }

    public function method()
    {
        return array_get($this->attributes, 'method', array_get($this->attributes, 'function', ''));
    }

    public function args()
    {
        if (empty($this->attributes['args'])) {
            return [];
        }
        $args = [];
        $names = $this->getParameterNames();
        foreach ($this->attributes['args'] as $key => $val) {
            $args[array_get($names, $key, "param$key")] = $val;
        }

        return $args;
    }

    /**
     * @param \ReflectionParameter[] $parameterReflections
     */
    public function getParameterNames()
    {
        $names = [];
        $class = array_get($this->attributes, 'class');
        $method = array_get($this->attributes, 'method');
        if ($class && isset($method)) {
            $classReflection = new \ReflectionClass($class);
            if (!$classReflection->hasMethod($method)) {
                return $names;
            }
            foreach ($classReflection->getMethod($method)->getParameters() as $reflection) {
                $names[] = $reflection->getName();
            }
        }

        return $names;
    }

    protected function extractArgs($args)
    {
        if (empty($args)) {
            return [];
        }
        $args = explode(',', $args);

        return array_map('trim', $args);
    }

    public function line()
    {
        return array_get($this->attributes, 'line', 0);
    }

    public function __call($method, $arguments = [])
    {
        return array_get($this->attributes, $method, '');
    }

    public function __get($key)
    {
        return array_get($this->attributes, $key, '');
    }
}
