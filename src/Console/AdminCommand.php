<?php

namespace Swoft\Admin\Console;

use Swoft\Admin\Admin;
use Swoft\Admin\Console\Traits\MoveDirectory;
use Swoft\Admin\Util;
use Swoft\Console\Bean\Annotation\Command;
use Swoft\Console\Bean\Annotation\Mapping;
use Swoft\Console\Input\Input;
use Swoft\Console\Output\Output;
use Swoft\Admin\Console\Traits\MoveAdminConfig;

/**
 * @Command(name="admin",coroutine=false)
 */
class AdminCommand
{
    use MoveAdminConfig, MoveDirectory;

    /**
     * 安装
     *
     * @Usage
     * admin:{command} [arguments] [options]
     *
     * @Options
     *
     * @Example
     * php swoft admin:install
     *
     * @param Input  $input
     * @param Output $output
     *
     * @Mapping("install")
     */
    public function install(Input $input, Output $output)
    {
        $output->writeln("\n[SWOFT ADMIN] 开始安装...");
        $output->writeln("\n请确保您的程序拥有以下目录的<warning>读写权限</warning>");
        $output->writeln("*    <warning>@root/public</warning>");
        $output->writeln("*    <warning>@root/config</warning>");
        $output->writeln("*    <warning>@root/resources</warning>");
        $output->writeln("----------------------------------\n");

        if (!Util::isWriteable(alias('@root/public'))) {
            $output->colored('检测到目录 @root/public 没有写权限，安装失败！', 'error');
            return;
        }
        if (!Util::isWriteable(alias('@root/config'))) {
            $output->colored('检测到目录 @root/config 没有写权限，安装失败！', 'error');
            return;
        }
        if (!Util::isWriteable(alias('@root/resources'))) {
            $output->colored('检测到目录 @root/resources 没有写权限，安装失败！', 'error');
            return;
        }

        // 移动admin.php
        if ($this->moveAdminConfig($output) === false) {
            $output->colored("安装失败！", 'error');
            return;
        }

        // 移动模板文件
        $this->moveViews($output);

        // 移动语言包
        $this->moveLangPackages($output);

        // 移动静态资源文件
        $this->moveAssets($output);

        $output->colored("恭喜您安装完成啦！", 'success');
    }

    /**
     * 引入扩展
     *
     * @Usage
     * admin:{command} [arguments] [options]
     *
     * @Options
     *
     * @Example
     * php swoft admin:import
     *
     * @param Input  $input
     * @param Output $output
     *
     * @Mapping("import")
     */
    public function import(Input $input, Output $output)
    {
        $output->writeln("[SWOFT ADMIN] 开始导入扩展...\n");

        $extensions = Admin::getExtenstionClass();
        if (!$extensions) {
            $output->colored("您没有安装任何扩展", 'warning');
            return;
        }

        foreach ($extensions as $class) {
            $output->writeln("开始导入 <info>$class</info>...");
            $class::make()->import($output);
            $output->writeln(' ');
        }
    }

}
