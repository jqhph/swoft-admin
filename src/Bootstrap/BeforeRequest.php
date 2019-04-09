<?php

namespace Swoft\Admin\Bootstrap;

use Swoft\Admin\Admin;
use Swoft\Admin\Debugger\Collector;
use Swoft\Bean\Annotation\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Swoft\Http\Server\Event\HttpServerEvent;
use Swoft\Http\Server\ServerDispatcher;

/**
 * 请求前
 *
 * @Listener(HttpServerEvent::BEFORE_REQUEST)
 */
class BeforeRequest implements EventHandlerInterface
{
    private $addMiddlewares = false;

    /**
     * 事件回调
     *
     * @param EventInterface $event      事件对象
     */
    public function handle(EventInterface $event)
    {
        Collector::start();

        // 触发扩展类onBeforeRequest方法
        $this->fireExtensionEvent();

        // 添加公共中间件
        $this->addPublicMiddlewares();
    }

    /**
     * 添加公共中间件
     */
    protected function addPublicMiddlewares()
    {
        if ($this->addMiddlewares) {
            return;
        }
        $this->addMiddlewares = true;

        /* @var ServerDispatcher $dispatcher */
        $dispatcher = bean('serverDispatcher');

        $dispatcher->addMiddleware(PjaxMiddleware::class);

        if (Admin::isDebug() && config('admin.debug-console')) {
            $dispatcher->addMiddleware(DebugMiddleware::class);
        }

        // 异常处理中间件
        $this->addExceptionMiddleware($dispatcher);

        // csrf token验证中间件
        $dispatcher->addMiddleware(VerifyCsrfToken::class);
    }

    /**
     * 添加异常处理中间件
     *
     * @param ServerDispatcher $dispatcher
     */
    private function addExceptionMiddleware(ServerDispatcher $dispatcher)
    {
        if (!Admin::isDebug() || !config('admin.exception-report')) {
            return;
        }
        $dispatcher->addMiddleware(ExceptionHandleMiddleware::class);
    }

    /**
     * 触发扩展类onBeforeRequest方法
     */
    private function fireExtensionEvent()
    {
        foreach (Admin::getExtensions() as $extension) {
            $extension->onBeforeRequest();
        }

    }
}
