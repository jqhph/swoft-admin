<?php

namespace Swoft\Admin\Console\Traits;

use Swoft\Console\Output\Output;

trait MoveDirectory
{
    /**
     * 移动模板文件
     *
     * @param Output $output
     * @return void
     */
    protected function moveViews(Output $output)
    {
        $target = '@root/resources/views/swoft-admin';

        $output->writeln("[步骤二] 开始移动模板文件到 <info>$target</info> 目录");

        if (is_dir(alias($target))) {
            $output->writeln("检测到模板目录 <warning>$target</warning> 已存在，移动模板文件结束\n");
            return;
        }

        filesystem()->copyDirectory(__DIR__.'/../../../resources/views', alias($target));

        $output->colored("模板文件移动成功\n", 'success');
    }

    /**
     * 移动语言包
     *
     * @param Output $output
     * @return void
     */
    protected function moveLangPackages(Output $output)
    {
        $target = '@root/resources/languages';

        $output->writeln("[步骤三] 开始移动语言包到 <info>$target</info> 目录");

        $currentLang = 'zh-CN';
        if (is_file(alias($target).'/'.$currentLang.'/admin.php')) {
            $output->writeln("检测到语言包 <warning>$target/$currentLang/admin.php</warning> 已存在，移动语言包结束\n");
            return;
        }

        filesystem()->copyDirectory(__DIR__.'/../../../resources/lang', alias($target));

        $output->colored("语言包移动成功\n", 'success');
    }

    /**
     * 移动静态资源文件
     *
     * @param Output $output
     * @return void
     */
    protected function moveAssets(Output $output)
    {
        $target = '@root/public/assets/swoft-admin';

        $output->writeln("[步骤四] 开始移动静态资源文件到 <info>$target</info> 目录");

        if (is_dir(alias($target))) {
            $output->writeln("检测到静态资源目录 <warning>$target</warning> 已存在，移动静态资源文件结束\n");
            return;
        }

        filesystem()->copyDirectory(__DIR__.'/../../../resources/assets', alias($target));

        $output->colored("静态资源文件移动成功\n", 'success');
    }
}
