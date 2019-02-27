<?php

namespace Swoft\Admin\Debugger;

use Monolog\Handler\AbstractProcessingHandler;
use Swoft\Admin\Admin;
use Swoft\Admin\Exception\BacktraceFormatter;
use Swoft\Core\RequestContext;
use Swoft\Log\Logger;

/**
 * 调试日志handler
 */
class DebugHandler extends AbstractProcessingHandler
{
    /**
     * @var array 输出包含日志级别集合
     */
    protected $levels = [
        Logger::DEBUG,
    ];

    /**
     * 输出到文件
     *
     * @param array $record 日志记录集合
     */
    protected function write(array $record)
    {
        if (!$this->isHandling($record)) {
            return;
        }
        $message = &$record['messages'];
        $extra   = $record['extra'] ?: $record['context'];
        $traces  = array_get($record, 'traces');
        $file    = $this->getTraceFile($message);
        $sql     = $this->getSql($message);
        $sqlId   = $this->getSqlId($message);
        /* @var \DateTime $date */
        $date    = $record['datetime'];

        if ($sql) {
            $traces = $traces ? (new BacktraceFormatter(array_slice($traces, 8, 16)))->toString() : '';
            Collector::collectDatabaseQuery($sql, [], null, $file, $sqlId, $traces);
        } else {
            Collector::collectDebugLog($message, $extra, $file, $date->format('Y-m-d H:i:s'));
        }
    }

    /**
     * 获取日志写入文件及行数
     *
     * @param string $message
     * @return string
     */
    protected function getTraceFile(string &$message) {
        $trace = preg_match('/(trace\[.*\]) /', $message, $m) ? $m[1] : '';

        $message = str_replace($trace, '', $message);

        return str_replace(['trace[', ']'], ['', ''], $trace);
    }

    protected function getSqlId(string $message)
    {
        $trace = preg_match('/sqlId=([a-z0-9_A-Z]*)/', $message, $m) ? $m[1] : '';
        return str_replace('sqlId=', '', $trace);
    }

    protected function getSql(string $message)
    {
        $trace = preg_match('/(sql=.*)/', $message, $m) ? $m[1] : '';
        return str_replace('sql=', '', $trace);
    }

    /**
     * check是否输出日志
     *
     * @param array $record
     *
     * @return bool
     */
    public function isHandling(array $record)
    {
        if (!RequestContext::getRequest() || !Admin::isDebug() || !config('admin.debug-console')) {
            return false;
        }

        if (empty($this->levels)) {
            return true;
        }

        return in_array($record['level'], $this->levels);
    }

}
