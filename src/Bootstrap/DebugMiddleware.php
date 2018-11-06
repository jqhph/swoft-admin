<?php

namespace Swoft\Admin\Bootstrap;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoft\Admin\Admin;
use Swoft\Admin\Debugger\Collector;
use Swoft\Bean\Annotation\Bean;
use Swoft\Http\Message\Middleware\MiddlewareInterface;
use Swoft\Http\Message\Stream\SwooleStream;
use Swoft\Http\Server\AttributeEnum;

/**
 * 输出debug信息到前端
 *
 * @Bean()
 */
class DebugMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!Admin::isAjaxRequest() || !Admin::isDebug()) {
            return $handler->handle($request);
        }

        $resp = $handler->handle($request);
        $data = $resp->getBody()->getContents() ?: $resp->getAttribute(AttributeEnum::RESPONSE_ATTRIBUTE);
        $collector = Collector::make()->format();

        if ($this->isRedirect($resp)) {
            $this->setCurrentDebugData($collector);

            return $resp;
        }

        if (Admin::isPjaxRequest()) {
            $data .= $this->buildDebugDataPjax($collector);

        } else {
            $array = is_array($data) ? $data : json_decode($data, true);

            if (is_array($array) || empty($data)) {
                if (empty($data)) {
                    $array = [];
                }
                $array['__traces__'] = $collector->toArray();
                $data = json_encode($array);
            }
        }

        return $resp->withBody(new SwooleStream($data));
    }

    /**
     * @param Collector $collector
     * @return string
     */
    protected function buildDebugDataPjax(Collector $collector)
    {
        $prev = $this->getPrevRequestDebugData();

        $json = $collector->toJson();
        return <<<EOF
<script>
(function () {
    var prev = $prev;
    prev && DEBUGGER.add(prev);
    DEBUGGER.add({$json});
    DEBUGGER.rerender();
})();
</script>
EOF;
    }

    /**
     * @return string
     */
    protected function getPrevRequestDebugData()
    {
        $session = session();

        if (!$prev = $session->remove('__prev_req_debug__')) {
            return 0;
        }
        $prev['route']['status'] = 302;
        $prev = json_encode($prev);

        return $prev;
    }

    protected function setCurrentDebugData(Collector $collector)
    {
        session()->put('__prev_req_debug__', $collector->toArray());
    }

    protected function isRedirect($resp)
    {
        return $resp->getStatusCode() == 302;
    }
}
