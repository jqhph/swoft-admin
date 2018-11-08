<?php

namespace Swoft\Admin\Exception;

use Psr\Http\Message\ResponseInterface;
use Swoft\Admin\Admin;
use Swoft\Admin\Exception\Tracer\Parser;
use Swoft\Admin\Widgets\Card;
use Swoft\Admin\Widgets\Panel;
use Swoft\App;
use Swoft\Core\RequestContext;
use Swoft\Http\Server\Exception\NotAcceptableException;
use Swoft\Http\Server\Exception\RouteNotFoundException;
use Swoft\Support\MessageBag;
use Swoft\Support\ViewErrorBag;
use Swoft\Http\Message\Server\Request;

class Handler
{
    /**
     * 注册错误处理器
     * 只有debug模式有效
     */
    public static function registerErrorHandler()
    {
        if (!Admin::isDebug()) {
            return;
        }
        set_error_handler(function ($code, $msg, $file, $line) {
            $trace = debug_backtrace();

            $request = RequestContext::getRequest();
            // 非http请求, 则单纯打印错误信息
            if (!$request instanceof Request) {
                new PrintError($code, $msg, $file, $line, $trace);
                return;
            }

            (new \Whoops\Run)->handleError($code, $msg, $file, $line);
        }, E_ALL | E_STRICT);
    }

    /**
     * 把异常转化为错误追踪页面返回
     * 如果没有开启 debug 模式, 则会返回null
     *
     * @param \Throwable $e
     * @return ResponseInterface|null
     */
    public static function response(\Throwable $e)
    {
        if (
            (is_ajax_request() && !is_pjax_request()) ||
            (is_ajax_request() && in_array(request()->getMethod(), ['POST', 'PUT', 'DELETE']))
        ) {
            return self::responseJson($e);
        }

        // 路由不存在
        if ($e instanceof NotAcceptableException) {
            $request = RequestContext::getRequest();
            if ($request && $request->getUri()->getPath() == '/favicon.ico') {
                return \response()->withStatus(302);
            }
        }
        if ($e instanceof RouteNotFoundException) {
            return build404page()
                ->toResponse()
                ->withStatus(404);
        }
        if (!Admin::isDebug()) {
            // 非debug环境不做任何处理
            return;
        }

        if (!config('admin.use-whoops')) {
            return self::responseWithDefault($e);
        }

        // 使用 whoops 插件渲染错误信息
        return self::responseWithWhoops($e);
    }

    /**
     * @param \Throwable $e
     * @return ResponseInterface
     */
    protected static function responseWithWhoops(\Throwable $e)
    {
        $whoops = new \Whoops\Run;

        $handler = new \Whoops\Handler\PrettyPageHandler;
        $handler->handleUnconditionally(true);

        $whoops->pushHandler($handler);
        $whoops->writeToOutput(false);
        $whoops->allowQuit(false);

        return html_response($whoops->handleException($e))->withStatus(500);
    }

    /**
     * 使用系统内置错误信息处理功能渲染错误信息
     *
     * @param \Throwable $e
     * @return ResponseInterface
     */
    protected static function responseWithDefault(\Throwable $e)
    {
        return Admin::content()
            ->body(static::renderException($e))
            ->response()
            ->withStatus(500);
    }

    /**
     * 响应json数据到前端
     *
     * @param \Throwable $e
     * @return ResponseInterface
     */
    protected static function responseJson(\Throwable $e)
    {
        $data = [
            'status' => 0,
            'data' => [],
            'error' => [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ],
        ];

        return response()->json($data, 500);
    }

    /**
     * Render exception.
     *
     * @param \Throwable $e
     *
     * @return string
     */
    public static function renderException(\Throwable $e)
    {
        return static::buildPage(
            get_class($e),
            $e->getMessage(),
            $e->getCode(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
    }

    /**
     * @param string $exceptionClass
     * @param string $message
     * @param int $code
     * @param string $file
     * @param $line
     * @param string $trace
     * @return string
     */
    protected static function buildPage(
        string $exceptionClass,
        string $message,
        int $code,
        string $file,
        $line,
        string $trace
    )
    {
        $cls = $exceptionClass;

        $error = new MessageBag([
            'type'    => $cls,
            'message' => $message,
            'file'    => $file,
            'line'    => $line,
            'code'    => $code,
        ]);

        $errors = new ViewErrorBag();
        $errors->put('exception', $error);

        $first = "#0 {$file}({$line})\n";
        $frames = (new Parser($first.$trace))->parse();

        Admin::js('@admin/prism/prism.js?_='.time());
        Admin::css('@admin/prism/prism.css');

        $data = ['errors' => $errors, 'frames' => $frames];

        return (new Card(null, blade('admin::partials.trace', $data)))->render();
    }
    
}
