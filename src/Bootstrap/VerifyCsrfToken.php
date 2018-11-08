<?php

namespace Swoft\Admin\Bootstrap;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoft\Admin\Admin;
use Swoft\Bean\Annotation\Bean;
use Swoft\Core\RequestContext;
use Swoft\Http\Message\Middleware\MiddlewareInterface;
use Swoft\Support\Input;

/**
 * CsrfToken验证中间件
 *
 * @Bean()
 */
class VerifyCsrfToken implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (
            (!config('admin.csrf.enable')) ||
            $this->isReading($request) ||
            $this->inExceptArray($request) ||
            $this->tokensMatch($request)
        ) {
            return $handler->handle($request);
        }

        return $this->responseError();
    }

    /**
     * 读取请求判断
     *
     * @param  RequestInterface $request
     * @return bool
     */
    protected function isReading($request)
    {
        return in_array($request->getMethod(), ['HEAD', 'GET', 'OPTIONS']);
    }

    /**
     * 判断是否是需要跳过token验证的路径
     *
     * @param  RequestInterface  $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        $path = $request->getUri()->getPath();

        foreach ((array) config('admin.csrf.except') as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($path === $except) {
                return true;
            }
        }

        return false;
    }

    /**
     * 验证token
     *
     * @param  RequestInterface  $request
     * @return bool
     */
    protected function tokensMatch($request)
    {
        $token = $this->getTokenFromRequest($request);
        $serverToken = session()->token();

        return is_string($serverToken) &&
            is_string($token) &&
            hash_equals($serverToken, $token);
    }

    /**
     * 获取token
     *
     * @param  RequestInterface  $request
     * @return string
     */
    protected function getTokenFromRequest($request)
    {
        return Input::make()->request('_token') ?: ($request->getHeader('XSRF-TOKEN')[0] ?? '');
    }

    /**
     * 返回错误信息
     *
     * @return ResponseInterface
     */
    protected function responseError()
    {
        if (is_pjax_request()) {
            admin_warning('419', 'Sorry, your session has expired. Please refresh and try again.');
            return redirect_refresh();
        }

        if (is_ajax_request()) {
            return RequestContext::getResponse()->withStatus(419)->json([
                'status' => false,
                'msg' => 'Sorry, your session has expired. Please refresh and try again.',
            ]);
        }

        $data = [
            'title' => 419,
            'error' => 419,
            'message' => 'Sorry, your session has expired. Please refresh and try again.'
        ];
        return html_response(blade('admin::partials.error', $data))->withStatus(500);
    }

}