<?php

namespace Swoft\Admin\Bootstrap;

use Swoft\Admin\Exception\Handler;
use Swoft\Bean\Annotation\BeforeStart;
use Swoft\Bootstrap\Listeners\Interfaces\BeforeStartInterface;
use Swoft\Bootstrap\Server\AbstractServer;

/**
 * @BeforeStart()
 */
class BeforeServerStart implements BeforeStartInterface
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @param AbstractServer $server
     * @throws \Swoft\Exception\InvalidArgumentException
     */
    public function onBeforeStart(AbstractServer $server)
    {
        Handler::registerErrorHandler();
    }

}
