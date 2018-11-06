<?php

namespace Swoft\Admin\Debugger;

use Swoft\Admin\Admin;
use Swoft\App;
use Swoft\Core\Coroutine;
use Swoft\Core\RequestContext;
use Swoft\Db\Executor;
use Swoft\Db\Model;
use Swoft\Db\QueryBuilder;

class Logger extends \Swoft\Log\Logger
{
    /**
     * 记录日志
     *
     * @param int   $level   日志级别
     * @param mixed $message 信息
     * @param array $context 附加信息
     * @return bool
     */
    public function addRecord($level, $message, array $context = array())
    {
        if (!Admin::isDebug()) {
            return parent::addRecord($level, $message, $context);
        }
        $levelName = static::getLevelName($level);

        if (! static::$timezone) {
            static::$timezone = new \DateTimeZone(date_default_timezone_get() ? : 'UTC');
        }

        // php7.1+ always has microseconds enabled, so we do not need this hack
        if ($this->microsecondTimestamps && PHP_VERSION_ID < 70100) {
            $ts = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)), static::$timezone);
        } else {
            $ts = new \DateTime(null, static::$timezone);
        }

        $ts->setTimezone(static::$timezone);

        $message = $this->formatMessage($message);
        $message = $this->getTrace($message);
        $record = $this->formateRecord($message, $context, $level, $levelName, $ts, []);

        foreach ($this->processors as $processor) {
            $record = \Swoole\Coroutine::call_user_func($processor, $record);
        }

        $this->messages[] = $record;

        if ($this->flushable($level)) {
            $this->flushLog();
        }

        return true;
    }

    /**
     * @param $level
     * @return bool
     */
    protected function flushable($level)
    {
        return App::$isInTest || \count($this->messages) >= $this->flushInterval || ($level === static::DEBUG && Admin::isDebug());
    }

    /**
     * 计算调用trace
     *
     * @param $message
     * @return string
     */
    public function getTrace($message): string
    {
        if (!Admin::isDebug()) {
            return parent::getTrace($message);
        }
        $traces = debug_backtrace();

        $count = \count($traces);
        $ex = '';
        if ($count >= 7) {
            $info = $traces[6];
            if (isset($info['file'], $info['line'])) {
                $filename = Collector::removeFilePrefix($info['file']);
                $lineNum = $info['line'];
                $ex = "$filename:$lineNum";
            }
        }
        if ($count >= 8) {
            $info = $traces[7];
            if (isset($info['class'], $info['type'], $info['function'])) {
                $ex .= ',' . $info['class'] . $info['type'] . $info['function']. ":{$traces[6]['line']}";;
            } elseif (isset($info['function'])) {
                $ex .= ',' . $info['function']. ":{$traces[6]['line']}";;
            }
        }
        if (strpos($message, 'sql=') !== false) {
            $index = 0;
            foreach ($traces as $k => &$info) {
                if (empty($info['class'])) continue;
                if ($info['class'] == QueryBuilder::class && $info['function'] != 'execute') {
                    $index = $k;
                }
                if ($info['class'] == Executor::class) {
                    $index = $k;
                }
                if ($info['class'] == Model::class) {
                    $index = $k;
                }
            }
            $info = $traces[$index ?: 10];

            $ex = Collector::removeFilePrefix($info['file']) . ':' . $info['line'];
        }

        if (!empty($ex)) {
            $message = "trace[$ex] " . $message;
        }
        return $message;
    }

    /**
     * 获取sql执行时间
     *
     * @param $mysqlId
     * @return string
     */
    public function getMysqlQueryCost($mysqlId)
    {
        $tid = Coroutine::tid();
        if (empty($this->profiles[$tid])) {
            return '';
        }

        $key = "mysql.$mysqlId";

        return round(($this->profiles[$tid][$key]['cost'] ?? 0) * 1000, 2);
    }

    /**
     * 格式化一条日志记录
     *
     * @param string    $message   信息
     * @param array     $context    上下文信息
     * @param int       $level     级别
     * @param string    $levelName 级别名
     * @param \DateTime $ts        时间
     * @param array     $extra     附加信息
     * @return array
     */
    public function formateRecord($message, $context, $level, $levelName, $ts, $extra)
    {
        if (!Admin::isDebug()) {
            return parent::formateRecord($message, $context, $level, $levelName, $ts, $extra);
        }
        $record = array(
            'logid'      => RequestContext::getLogid(),
            'spanid'     => RequestContext::getSpanid(),
            'messages'   => $message,
            'context'    => $context,
            'level'      => $level,
            'level_name' => $levelName,
            'channel'    => $this->name,
            'datetime'   => $ts,
            'extra'      => $extra,
        );

        $record['traces'] = debug_backtrace();

        return $record;
    }
}
