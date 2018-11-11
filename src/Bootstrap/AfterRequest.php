<?php

namespace Swoft\Admin\Bootstrap;

use Swoft\Admin\Admin;
use Swoft\Bean\Annotation\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Swoft\Http\Server\Event\HttpServerEvent;

/**
 * 请求后
 *
 * @Listener(HttpServerEvent::AFTER_REQUEST)
 */
class AfterRequest implements EventHandlerInterface
{
    /**
     * 事件回调
     *
     * @param EventInterface $event      事件对象
     */
    public function handle(EventInterface $event)
    {
        foreach (Admin::getExtensions() as $extension) {
            $extension->onAfterRequest();
        }
    }
}
