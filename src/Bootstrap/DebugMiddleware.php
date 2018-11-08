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
        if (!is_ajax_request() || !Admin::isDebug()) {
            return $handler->handle($request);
        }

        $resp = $handler->handle($request);
        $data = $resp->getBody()->getContents() ?: $resp->getAttribute(AttributeEnum::RESPONSE_ATTRIBUTE);
        $collector = Collector::make()->format();

        if ($this->isRedirect($resp)) {
            $this->setCurrentDebugData($collector);

            return $resp;
        }

        if ($this->isPjaxReading()) {
            $data .= $this->buildDebugDataForPjax($collector);
        } else {
            $data = $this->buildDebugDataForAjax($collector, $resp, $data);
        }

        return $resp->withBody(new SwooleStream($data));
    }

    /**
     * @return bool
     */
    protected function isPjaxReading()
    {
        return is_pjax_request() && !in_array(request()->getMethod(), ['POST', 'PUT', 'DELETE']);
    }

    /**
     * @param Collector $collector
     * @param ResponseInterface $response
     * @param $responseData
     * @return string
     */
    protected function buildDebugDataForAjax(
        Collector $collector,
        ResponseInterface $response,
        $responseData
    )
    {
        $array = is_array($responseData) ? $responseData : json_decode($responseData, true);

        if (is_array($array) || empty($responseData)) {
            if (empty($responseData)) {
                $array = [];
            }
            $debugInfo = $collector->toArray();
            $debugInfo['route']['status'] = $response->getStatusCode();

            $array['__traces__'] = $debugInfo;
            return json_encode($array);
        }

        return $responseData;
    }


    /**
     * @param Collector $collector
     * @return string
     */
    protected function buildDebugDataForPjax(Collector $collector)
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
