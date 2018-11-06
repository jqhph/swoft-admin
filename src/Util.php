<?php

namespace Swoft\Admin;

class Util
{
    /**
     * 判断文件、文件夹是否可写
     *
     * @param string $path
     * @return bool
     */
    public static function isWriteable(string $path)
    {
        if (is_file($path)) {
            return is_writeable($path);
        }

        $path = rtrim($path, '/').'/__tmp.txt';
        if (@file_put_contents($path, 1)) {
            @unlink($path);
            return true;
        }
        return false;
    }
}
