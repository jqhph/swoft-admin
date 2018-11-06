<?php

use Swoft\Admin\Admin;
use Swoft\Support\MessageBag;
use Swoft\Support\Url;
use Swoft\Support\Assets;
use Psr\Http\Message\ResponseInterface;
use Swoft\Support\SessionHelper;

if (!function_exists('admin_path')) {

    /**
     * Get admin path.
     *
     * @param string $path
     *
     * @return string
     */
    function admin_path($path = '')
    {
        return ucfirst(config('admin.directory')).($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (!function_exists('admin_url')) {
    /**
     * Get admin url.
     *
     * @param string $path
     * @param mixed  $parameters
     *
     * @return string
     */
    function admin_url($path = '', $parameters = [])
    {
        if (Url::isValidUrl($path)) {
            return $path;
        }

        return Url::to(admin_base_path($path), $parameters);
    }
}

if (!function_exists('admin_base_path')) {
    /**
     * Get admin url.
     *
     * @param string $path
     *
     * @return string
     */
    function admin_base_path($path = '')
    {
        $prefix = '/'.trim(config('admin.route.prefix'), '/');

        $prefix = ($prefix == '/') ? '' : $prefix;

        if ($path === '/') {
            return $prefix ?: $path;
        }

        return $path ? $prefix.'/'.trim($path, '/') : $prefix;
    }
}

if (!function_exists('build404page')) {
    /**
     * 创建404页面
     *
     * @return \Swoft\Blade\Contracts\View
     */
    function build404page()
    {
        $data = [
            'title' => 404,
            'error' => '404 Not Found',
            'message' => 'Sorry, the page you are looking for could not be found.'
        ];
        return blade('admin::partials.error', $data);
    }
}

if (!function_exists('get_admin_notice')) {
    /**
     * @return MessageBag|null
     */
    function get_admin_notice()
    {
        if (Admin::hasContextAttribute('__notice')) {
            return Admin::getContextAttribute('__notice');
        }
        $session = SessionHelper::wrap();

        $notice = $session ? $session->pull('__notice') : null;

        Admin::setContextAttribute('__notice', $notice);

        return $notice;
    }
}

if (!function_exists('admin_notice')) {
    /**
     * Flash a layer message bag to session.
     *
     * @param string $message
     * @param string $type
     * @param string $offset
     */
    function admin_notice($message = '', string $type = 'success', string $offset = 't')
    {
        $notice = new MessageBag(get_defined_vars());

        $session = SessionHelper::wrap();

        $session ? $session->put('__notice', $notice) : null;

        Admin::setContextAttribute('__notice', $notice);
    }
}

if (!function_exists('admin_success')) {

    /**
     * Flash a success message bag to session.
     *
     * @param string $title
     * @param string $message
     */
    function admin_success($title, $message = '')
    {
        admin_info($title, $message, 'success');
    }
}

if (!function_exists('admin_error')) {

    /**
     * Flash a error message bag to session.
     *
     * @param string $title
     * @param string $message
     */
    function admin_error($title, $message = '')
    {
        admin_info($title, $message, 'danger');
    }
}

if (!function_exists('admin_warning')) {

    /**
     * Flash a warning message bag to session.
     *
     * @param string $title
     * @param string $message
     */
    function admin_warning($title, $message = '')
    {
        admin_info($title, $message, 'warning');
    }
}

if (!function_exists('admin_info')) {

    /**
     * Flash a message bag to session.
     *
     * @param string $title
     * @param string $message
     * @param string $type
     */
    function admin_info($title, $message = '', $type = 'info')
    {
        $message = new MessageBag(get_defined_vars());

        $key     = '_admin_msg_';
        $session = SessionHelper::wrap();

        $data = null;
        if ($session) {
            $session->push($key, $message);
            $data = $session->get($key);
        }

        Admin::setContextAttribute($key, $data ?: [$message]);
    }
}

if (!function_exists('get_flash_message')) {
    /**
     *
     * @return MessageBag[]
     */
    function admin_flash_message()
    {
        $key = '_admin_msg_';

        if (Admin::hasContextAttribute($key)) {
            return Admin::getContextAttribute($key);
        }

        $session = SessionHelper::wrap();

        $message = $session ? $session->pull($key) : [];

        Admin::setContextAttribute($key, $message);

        return $message;
    }
}

if (!function_exists('flash_errors_each')) {
    /**
     * 批量设置
     *
     * @param array $errors
     */
    function flash_errors_each(array $errors) {
        foreach ($errors as $k => &$v) {
            flash_errors($v, $k);
        }
    }
}

if (!function_exists('flash_errors')) {
    /**
     * 存储错误信息
     *
     * @param \Swoft\Support\Contracts\MessageProvider|array|string $provider
     * @param string $key
     * @return $this
     */
    function flash_errors($provider, string $key = 'default')
    {
        $name = '__errors__';

        if ($provider instanceof \Swoft\Support\Contracts\MessageProvider) {
            $value = $provider->getMessageBag();
        } else {
            $value = new MessageBag((array) $provider);
        }

        $session = SessionHelper::wrap();

        $errors = $session->get($name, new \Swoft\Support\ViewErrorBag());

        if (! $errors instanceof \Swoft\Support\ViewErrorBag) {
            $errors = new \Swoft\Support\ViewErrorBag();
        }

        $session->put(
            $name, $errors->put($key, $value)
        );
        Admin::setContextAttribute($name, $errors);
    }
}


if (!function_exists('get_flash_errors')) {
    /**
     * 获取错误消息
     *
     * @return \Swoft\Support\ViewErrorBag
     */
    function get_flash_errors() {
        $key = '__errors__';

        if (Admin::hasContextAttribute($key)) {
            return Admin::getContextAttribute($key);
        }

        $session = SessionHelper::wrap();

        $error = $session ? $session->pull($key) : null;
        $error = $error ?: new \Swoft\Support\ViewErrorBag();

        Admin::setContextAttribute($key, $error);

        return $error;

    }
}

if (!function_exists('flash_input')) {
    /**
     * 暂存用户输入数据
     *
     * @param array $input
     * @return null|void
     */
    function flash_input(array $input)
    {
        $session = SessionHelper::wrap();

        $session ? $session->put('__input__', $input) : null;

        Admin::setContextAttribute('__input__', $input);
    }
}

if (!function_exists('old_input')) {
    /**
     * 获取用户上次请求的GET/POST参数
     *
     * @param string $key
     * @param null $default
     * @return array|mixed
     */
    function old_input(string $key = null, $default = null)
    {
        if (Admin::hasContextAttribute('__input__')) {
            $input = Admin::getContextAttribute('__input__');
        } else {
            $session = SessionHelper::wrap();
            $input = (array)($session ? $session->pull('__input__') : []);
            Admin::setContextAttribute('__input__', $input);
        }
        if ($key === null) {
            return $input;
        }

        return array_get($input, $key, $default);
    }
}


if (!function_exists('admin_asset')) {
    /**
     * 获取admin后台的静态资源
     *
     * @param $path
     * @return string
     */
    function admin_asset($path)
    {
        return Assets::alias('@admin/'.ltrim($path,'/'));
    }
}

if (!function_exists('array_delete')) {
    /**
     * Delete from array by value.
     *
     * @param array $array
     * @param mixed $value
     */
    function array_delete(&$array, $value)
    {
        foreach ($array as $index => $item) {
            if ($value == $item) {
                unset($array[$index]);
            }
        }
    }
}

if (!function_exists('arr_merge')) {
    /**
     * 合并新的数组到旧的数组
     *
     * @param array $content
     * @param array $new
     * @param bool $recurrence
     * @return array
     */
    function arr_merge(array &$content, array &$new, $recurrence = false)
    {
        foreach ($new as $k => & $v) {
            if ($recurrence) {
                if (isset($content[$k]) && is_array($content[$k]) && is_array($v)) {
                    $content[$k] = arr_merge($content[$k], $v, true);
                    continue;
                }
            }

            $content[$k] = $v;
        }

        return $content;
    }
}

if (!function_exists('admin_debug')) {
    /**
     * 输入debug日志
     * 非debug环境下无效
     *
     * @param $msg
     * @param $data
     * @param string $type
     */
    function admin_debug($msg, $data = [], string $type = 'debug')
    {
        if (!Admin::isDebug()) {
            return;
        }
        if ($data instanceof \Swoft\Contract\Arrayable) {
            $data = $data->toArray();
        }
        \Swoft\Admin\Debugger\Collector::debug($msg, $data);
        debuglog($msg.' '.json_encode($data), [], $type);
    }
}

if (!function_exists('translate_field')) {
    /**
     * 翻译当前控制器数据表字段名称
     *
     * @param string $column
     * @return mixed
     */
    function translate_field(string $column)
    {
        return Admin::translateField($column);
    }
}

if (!function_exists('translate_label')) {
    /**
     * 翻译当前控制器labels字段
     *
     * @param string $column
     * @return mixed
     */
    function translate_label(string $column)
    {
        return Admin::translateLabel($column);
    }
}
