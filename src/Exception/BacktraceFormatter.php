<?php

namespace Swoft\Admin\Exception;

use Swoft\App;

class BacktraceFormatter
{
    /**
     * @var array
     */
    protected $trace = [];

    public function __construct(array $trace)
    {
        $this->trace = $trace;
    }

    /**
     * 获取转化成字符串后的结果
     *
     * @return string
     */
    public function toString(int $start = 0)
    {
        $string = '';
        foreach (array_slice($this->trace, $start) as $i => &$item) {
            $string .= $this->format($i, $item);
        }

        return $string;
    }

    /**
     * 格式化单条追踪信息
     *
     * @param int $num
     * @param array $debug
     * @return string
     */
    protected function format(int $num, array $debug)
    {
        $file = '';
        if (!empty($debug['file'])) {
            $file = "{$debug['file']}({$debug['line']}): ";
        }

        $call = $this->formatCallstring($debug);
        $args = '';
        if (!empty($debug['args'])) {
            $args = $this->formatArgs($debug['args']);
        }

        return "#{$num} {$file}{$call}{$args}\n";
    }

    protected function formatCallstring(array $debug)
    {
        if (!empty($debug['class'])) {
            return "{$debug['class']}{$debug['type']}{$debug['function']}";
        }
        return "{$debug['function']}";
    }

    /**
     * @param array $args
     * @return string
     */
    protected function formatArgs(array $args)
    {
        if (!$args) {
            return '()';
        }

        $result = [];
        foreach ($args as $k => $v) {
            if (is_string($v)) {
                if (class_exists($v)) {
                    $result[] = "Object($v)";
                    continue;
                }
                if (mb_strlen($v) > 20) {
                    $v = mb_substr($v, 0, 20).'...';
                }
                $result[] = '"'.$v.'"';
                continue;
            }
            if (is_object($v)) {
                $v = get_class($v);
                $result[] = "Object($v)";
            }

            if (is_array($v)) {
                $count = count($v);
                $result[] = "Array($count)";
                continue;
            }
            $result[] = $v;
        }

        return '('.join(', ', $result).')';
    }
}
