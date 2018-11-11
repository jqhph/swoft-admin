<?php

namespace Swoft\Admin\Exception;

use Swoft\App;

class PrintError
{
    public function __construct(int $code, string $msg, string $file, $line, array $trace)
    {
        $file = str_replace(App::getAlias('@root'), '', $file);
        $message = "$msg in $file($line)";

        $trace = array_slice($trace, 0, 6);
        $trace = (new BacktraceFormatter($trace))->toString();

        if (!App::hasBean('config')) {
            return var_dump($message, $trace);
        }
        if (static::isErrorForPhp($code)) {
            debuglog($message, [], 'error');
        } else {
            debuglog($message, [], 'warning');
        }

        consolelog($trace);
    }

    /**
     * 判断php错误级别是否error
     *
     * @param int $code
     * @return bool
     */
    public static function isErrorForPhp(int $code)
    {
        return in_array($code, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_DEPRECATED]);
    }

    /**
     * 判断是否是wraning级别错误
     *
     * @param int $code
     * @return bool
     */
    public static function isWarningForPhp(int $code)
    {
        return !static::isErrorForPhp($code);
    }


}
