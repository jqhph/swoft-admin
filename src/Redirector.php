<?php

namespace Swoft\Admin;

use Psr\Http\Message\ResponseInterface;

class Redirector
{
    /**
     * @var bool
     */
    protected $success;

    /**
     * @var null
     */
    protected $target = null;

    /**
     * @var ResponseInterface
     */
    protected $redirector;

    /**
     * @var ResponseInterface
     */
    protected $final;

    public function __construct(bool $success, $response = null)
    {
        $this->success = $success;

        $this->redirector = $this->normalizeResponse($response);
    }

    /**
     * @param string|ResponseInterface $url
     * @return ResponseInterface|null
     */
    protected function normalizeResponse($url)
    {
        if (!$url) {
            return null;
        }
        if ($url instanceof ResponseInterface) {
            return $url;
        }
        return redirect_to($url);
    }

    /**
     * 设置最终响应客户端的内容
     * 不受done方法设置的链接影响
     *
     * @param ResponseInterface|string $url
     * @return $this
     */
    public function final($url)
    {
        $this->final = $this->normalizeResponse($url);
        return $this;
    }

    /**
     *
     * @param string|null $success 成功后跳转链接
     * @param string|null $fail 失败后跳转链接
     * @return ResponseInterface
     */
    public function done(string $success = null, string $fail = null)
    {
        if ($this->final) {
            return $this->final;
        }

        $url = $this->success ? $success : $fail;

        if ($url) {
            if ($this->redirector) {
                return $this->redirector
                    ->withoutHeader('Location')
                    ->withHeader('Location', $url);
            }

            return redirect_to($url);
        }

        return $this->redirector ?:
            ($this->success ? redirect_back() : redirect_refresh());
    }


}
