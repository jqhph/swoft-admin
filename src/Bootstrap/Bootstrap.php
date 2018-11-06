<?php

namespace Swoft\Admin\Bootstrap;

use Swoft\Admin\Admin;
use Swoft\Admin\Extension;
use Swoft\Admin\Form;
use Swoft\Admin\Grid;
use Swoft\Bean\Annotation\ServerListener;
use Swoft\Bean\BeanFactory;
use Swoft\Bean\ObjectDefinition;
use Swoft\Bean\Resource\ServerAnnotationResource;
use Swoft\Bootstrap\Listeners\Interfaces\WorkerStartInterface;
use Swoft\Bootstrap\SwooleEvent;
use Swoft\Support\Assets;
use Swoole\Server;

/**
 * 缓存进程id，暂作废
 *
 * @ServerListener(SwooleEvent::ON_WORKER_START)
 */
class Bootstrap implements WorkerStartInterface
{
    public function onWorkerStart(Server $server, int $workerId, bool $isWorker)
    {
        // 注册视图命名空间
        blade_factory()->addNamespace('admin', alias(config('admin.views-path', __DIR__.'/../../resources/views')));

        // 注册静态资源别名
        Assets::setAlias([
            'admin' => alias(config('admin.assets-path', '/assets/swoft-admin'))
        ]);

        Register::registerFormBuiltinFields();
        Register::registerGridColumnDisplayer();
        $this->extend();

        if (is_file($bootstrap = admin_path('bootstrap.php'))) {
            require $bootstrap;
        }

        if (!empty(Admin::$booting)) {
            foreach (Admin::$booting as $callable) {
                call_user_func($callable);
            }
        }

        if (!empty(Admin::$booted)) {
            foreach (Admin::$booted as $callable) {
                call_user_func($callable);
            }
        }
    }

    /**
     * 初始化扩展
     *
     * @throws \Exception
     */
    protected function extend()
    {
        foreach (Admin::getExtenstionClass() as $class) {
            $extension = $class::make();

            if (!$extension instanceof Extension) {
                throw new \Exception("Extension class:$class must be an instance of Swoft\Admin\Extension");
            }

            $extension->boot();
        }
    }

}
