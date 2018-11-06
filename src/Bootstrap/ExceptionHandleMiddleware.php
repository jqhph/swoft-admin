<?php

namespace Swoft\Admin\Bootstrap;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoft\Admin\Admin;
use Swoft\Admin\Exception\Handler;
use Swoft\Bean\Annotation\Bean;
use Swoft\Http\Message\Middleware\MiddlewareInterface;

/**
 * debug模式下捕获异常并传递给Handler处理
 *
 * @Bean()
 */
class ExceptionHandleMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!Admin::isDebug()) {
            return $handler->handle($request);
        }
        try {
            return $handler->handle($request);
        } catch (\Throwable $e) {
            return Handler::response($e);
        }
    }
}
