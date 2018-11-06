<?php

namespace Swoft\Admin\Traits;

use Swoft\Admin\Util;
use Swoft\Console\Output\OutputInterface;
use Swoft\Migrations\Config;
use Swoft\Migrations\Console\Application;
use Swoft\Support\Translator;

trait ExtensionImporter
{
    /**
     * 导入模板文件
     *
     * @param OutputInterface $output
     */
    protected function importViews(OutputInterface $output)
    {
        $this->importFiles(
            $output,
            $this->views(),
            '@root/resources/views/admin-extensions/'.static::$name,
            '@root/resources/views'
        );
    }

    /**
     * 导入静态资源文件
     *
     * @param OutputInterface $output
     */
    protected function importAssets(OutputInterface $output)
    {
        $this->importFiles(
            $output,
            $this->assets(),
            '@root/public/assets/admin-extensions/'.static::$name,
            '@root/public/assets'
        );
    }

    /**
     * 导入数据库迁移文件
     *
     * @param OutputInterface $output
     */
    protected function importMigrations(OutputInterface $output)
    {
        $migrationPaths = Config::get('paths.migrations');
        $defaultPath = is_array($migrationPaths) ? current($migrationPaths) : $migrationPaths;
        if (!$defaultPath) {
            $defaultPath = '@root/resources/db/migrations';
        }

        $result = $this->importFiles(
            $output,
            $this->migrations(),
            $defaultPath,
            '@root/resources'
        );

        if ($result === true) {
            Application::call('migrate');
        }
    }

    /**
     * 导入语言包
     *
     * @param OutputInterface $output
     */
    protected function importLangs(OutputInterface $output)
    {
        $langDir = dirname(Translator::make()->currentPath());

        $this->importFiles(
            $output,
            $this->langs(),
            $langDir,
            dirname($langDir)
        );
    }

    /**
     * 导入文件
     *
     * @param OutputInterface $output
     * @param string $originalDir 需要导入的文件目录
     * @param string $targetDir 目标目录
     * @param string $targetParentDir 目标目录的上级目录(用于写权限检查)
     * @return true|void
     */
    protected static function importFiles(
        OutputInterface $output,
        $originalDir,
        $targetDir,
        $targetParentDir
    )
    {
        if (!is_dir($originalDir)) {
            return;
        }
        $target = alias($targetDir);

        if (is_dir($target)) {
            // 检测文件夹内是否有相同的文件,如果有则返回中断目录复制
            if ($exists = static::getIntersectFiles($originalDir, $target)) {
                $exists = join(',', $exists);
                $output->colored("检测到文件 $exists 已存在", 'warning');
                return;
            }
        }

        if (!Util::isWriteable(alias($targetParentDir))) {
            $output->colored("检测到目录 $targetParentDir 没有写权限", 'error');
            return;
        }

        filesystem()->copyDirectory(alias($originalDir), $target);
        $output->colored("导入 $originalDir 目录文件到 $targetDir 成功!\n", 'success');

        return true;
    }

    /**
     * 获取两个文件夹中重复的文件数组
     *
     * @param $dir1
     * @param $dir2
     * @return array
     */
    protected static function getIntersectFiles($dir1, $dir2)
    {
        return array_intersect(
            static::getFilenamesWithDir(alias($dir1)),
            static::getFilenamesWithDir(alias($dir2))
        );
    }

    protected static function getFilenamesWithDir($dir)
    {
        $dir = rtrim($dir, '/');
        $files = filesystem()->allFiles($dir);

        if (!$files) {
            return [];
        }

        $new = [];
        foreach ($files as $file) {
            $new[] = str_replace($dir, '', $file->getPathname());
        }

        return $new;
    }
}
