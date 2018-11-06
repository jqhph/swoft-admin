<?php

namespace Swoft\Admin\Console\Traits;

use Swoft\Console\Output\Output;

trait MoveAdminConfig
{
    /**
     * 移动配置文件
     *
     * @param Output $output
     * @return void|false
     */
    protected function moveAdminConfig(Output $output)
    {
        $output->writeln("[步骤一] 开始移动配置文件 <info>admin.php</info>");

        $filename = '@root/config/properties/admin.php';
        if (is_file(alias($filename))) {
            $output->writeln("检测到配置文件 <warning>$filename</warning> 已存在，请确认配置文件内容是否正确，移动配置文件结束\n");
            return;
        }

        if (!filesystem()->copy(__DIR__ . '/../../../config/admin.php', alias($filename))) {
            $output->colored("配置文件 $filename 移动失败，请确认是否拥有写权限\n", 'error');
            return false;
        }

        $this->appendConfigContent($output);

        $output->colored("配置文件移动成功\n", 'success');
    }

    protected function appendConfigContent(Output $output)
    {
        $append = '';

        if (config('admin')) {
            $output->writeln("检测到您已经配置了 <warning>admin</warning> 参数，请确认配置内容是否正确");
        }

        // 检测自定义组件配置是否存在
        if (!config('components.custom')) {
            $output->writeln("检测到您没有配置 <info>components.custom</info>，系统将自动为您配置");
            $append .= <<<EOF
    'components' => [
        'custom' => [
            'Swoft\\Admin',
            'Swoft\\Blade'
        ],
    ],
EOF;
        } else {
            $output->writeln("检测到您配置文件中已添加过 <warning>components.custom</warning> 配置，请在安装结束后手动在 <warning>components.custom</warning> 数组里面追加以下命名空间 ");
            $output->writeln('*    <warning>Swoft\Admin</warning> ');
            $output->writeln('*    <warning>Swoft\\Blade</warning>'."\n");
        }

        $append .= $this->getDefaultConfig();

        $files = filesystem();

        $app = alias('@root/config/properties/app.php');
        $content = $files->get($app);
        if (!$content) {
            $output->colored("读取配置文件 @root/config/properties/app.php 内容失败，请在安装程序结束后手动修改配置文件！");
            return;
        }

        $files->put($app, str_replace('];', $append, $content));
        $output->writeln("配置文件 <info>@root/config/properties/app.php</info> 已追加参数成功");
        (!config('components.custom')) && $output->writeln('*    <info>components.custom</info> ');
        $output->writeln('*    <info>admin</info>');
        $output->writeln('*    <info>blade-view</info>');
        $output->writeln('*    <info>assets</info>'."\n");
    }

    /**
     * @return string
     */
    protected function getDefaultConfig()
    {
        return <<<EOF
        
    /*
     | 静态资源帮助工具配置
     |
     */
    'assets' => [
        // 静态资源域名配置
        'resource-server' => '',

        // js文件请求后缀
        'js-version' => '',

        // css文件请求后缀
        'css-version' => '',

        // 静态资源别名配置
        'alias' => [

        ],
    ],

    /*
     | blade 模板引擎配置
     */
    'blade-view'   => [
        'path'     => '@root/resources/views',
        'compiled' => '@root/runtime/views',
        // 视图命名空间
        'namespaces' => [

        ],
        // 静态资源读取目录
        'assets' => [
            '@root/public'
        ],
        // 读取静态资源
        'read-assets' => false,
    ],
    
    'admin' => require __DIR__ . '/admin.php',
];
EOF;
    }
}
