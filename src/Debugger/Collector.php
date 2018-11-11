<?php

namespace Swoft\Admin\Debugger;

use Swoft\Admin\Admin;
use Swoft\Admin\Exception\BacktraceFormatter;
use Swoft\App;
use Swoft\Contract\Arrayable;
use Swoft\Core\RequestContext;
use Swoft\Support\SessionHelper;

class Collector implements Arrayable
{
    /**
     * @var array
     */
    public $values = [
        'queries' => [],
        'route' => [],
        'logs' => [],
        'session' => [],
        'start' => 0,
    ];

    /**
     * 標記請求開始
     */
    public static function start()
    {
        $collector = static::make();
        if (!$collector->values['start']) {
            $collector->values['start'] = microtime(true);
        }

        if (
            config('admin.debug-console')
            && Admin::isDebug()
        ) {
            Admin::js('@admin/AdminLTE/plugins/select2/select2.full.min.js');
            Admin::css('@admin/AdminLTE/plugins/select2/select2.min.css');
        }
    }

    /**
     * @param string $message
     * @param array $context
     */
    public static function debug(string $message, array $context = [])
    {
        static::collectDebugLog(
            $message,
            $context,
            static::getTrace(),
            date('Y-m-d H:i:s')
        );
    }

    /**
     * 追踪日志写入文件及行数
     *
     * @return string
     */
    public static function getTrace(): string
    {
        $traces = debug_backtrace();
        $count = \count($traces);
        $ex = '';
        if ($count >= 3) {
            $info = $traces[2];
            if (isset($info['file'], $info['line'])) {
                $filename = static::removeFilePrefix($info['file']);
                $lineNum = $info['line'];
                $ex = "$filename:$lineNum";
            }
        }
        return $ex;
    }

    /**
     * 收集数据库sql语句
     *
     * @param string $sql
     * @param array $bindings
     * @param $time
     * @param string $file
     * @param $sqlId
     * @param string $traces
     */
    public static function collectDatabaseQuery(
        string $sql,
        array $bindings,
        $time,
        string $file,
        $sqlId,
        string $traces
    )
    {
        $collector = static::make();

        $collector->values['queries'][] = [
            'sql' => $sql,
            'time' => $time ?: '',
            'file' => $file,
            'traces' => static::removeFilePrefix($traces),
            'id' => $sqlId,
        ];
    }

    /**
     * 移除文件路径前缀
     *
     * @param string $traces
     * @return string
     */
    public static function removeFilePrefix(string $traces)
    {
        return str_replace(App::getAlias('@root').'/', '', $traces);
    }


    /**
     * 收集debug日志
     *
     * @param string $content
     * @param array $context
     * @param string $file
     * @param string $date
     * @param string $traces
     */
    public static function collectDebugLog(
        string $content,
        array $context = [],
        string $file,
        string $date,
        string $traces = ''
    )
    {
        $collector = static::make();

        $collector->values['logs'][] = [
            'date' => date('H:i:s', strtotime($date)),
            'content' => &$content,
            'context' => $context,
            'file' => $file,
        ];
    }

    /**
     * 格式化已收集的调试信息
     *
     * @return $this
     */
    public function format()
    {
        $logger = App::getLogger();
        foreach ($this->values['queries'] as &$v) {
            $v['cost'] = $logger->getMysqlQueryCost($v['id']);
        }

        $this->collectRoutes();
        $this->collectSession();
        return $this;
    }

    /**
     * 收集路由请求信息
     */
    protected function collectRoutes()
    {
        $req = request();
        $uri = $req->getUri();

        $cost = microtime(true) - $this->values['start'];
        if ($cost >= 1) {
            $cost = round($cost, 2) .'s';
        } else {
            $cost = round($cost * 1000) . 'ms';
        }
        $controller = RequestContext::getContextDataByKey('controllerClass') .'@'
            .RequestContext::getContextDataByKey('controllerAction');

        $this->values['route'] = [
            'path' => $uri->getPath(),
            'method' => $req->getMethod(),
            'controller' => $controller,
            'type' => $this->getRequestType(),
            'query' => $req->query(),
            'post' => array_merge($req->post(), $req->json() ?: []),
            'reqtime' => date('H:i:s'),
            'datetime' => date('m-d H:i:s'),
            'cost' => $cost,
            'logid' => RequestContext::getContextDataByKey('logid'),
        ];
    }

    protected function getRequestType()
    {
        if (is_pjax_request()) {
            return 'pjax';
        }
        if (is_ajax_request()) {
            return  'ajax';
        }
        return 'doc';
    }

    /**
     * 收集session数据
     */
    protected function collectSession()
    {
        if (!$session = SessionHelper::wrap()) {
            return;
        }
        $all = $session->all();
        unset($all['__prev_req_debug__']);

        $this->values['session'] = &$all;
    }

    public function toArray(): array
    {
        return $this->values;
    }

    public function toJson()
    {
        return json_encode($this->values);
    }

    /**
     * 输出调试信息
     * @return string
     */
    public static function output()
    {
        return static::make()->format()->toJson();
    }

    /**
     * 获取控制器
     *
     * @return static
     */
    public static function make()
    {
        if ($collector = Admin::getContextAttribute('__debugger_collector__')) {
            return $collector;
        }
        $collector = new static();

        Admin::setContextAttribute('__debugger_collector__', $collector);

        return $collector;
    }
}
