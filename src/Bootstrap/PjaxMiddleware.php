<?php

namespace Swoft\Admin\Bootstrap;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoft\Bean\Annotation\Bean;
use Swoft\Http\Message\Middleware\MiddlewareInterface;

/**
 * @Bean()
 */
class PjaxMiddleware implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $resp = $handler->handle($request);

        return is_pjax_request() ? $this->setUriHeader($resp, $request) : $resp;
    }

    /**
     * Set the PJAX-URL header to the current uri.
     *
     * @param ResponseInterface $response
     * @param ServerRequestInterface  $request
     */
    protected function setUriHeader(ResponseInterface $response, ServerRequestInterface $request)
    {
        $uri = $request->getUri();

        return $response->withHeader(
            'X-PJAX-URL',
            $uri->getPath() . '?' . $uri->getQuery()
        );
    }
}
