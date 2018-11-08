<?php

namespace Swoft\Admin;

use Closure;
use Dotenv\Dotenv;
use Swoft\Admin\Auth\Database\Menu;
use Swoft\Admin\Layout\Content;
use Swoft\Admin\Repository\Repository;
use Swoft\Admin\Widgets\Navbar;
use Swoft\App;
use Swoft\Db\Bean\Collector\EntityCollector;
use Swoft\Db\Model;
use InvalidArgumentException;
use Swoft\Core\Coroutine;
use Swoft\Core\RequestContext;

/**
 * Class Admin.
 */
class Admin
{
    use Repository;

    /**
     * @var string
     */
    const VERSION = '1.0.0-dev';

    /**
     * @var array
     */
    protected static $attributes = [];

    /**
     * @var array
     */
    public static $script = [];

    /**
     * @var array
     */
    public static $css = [];

    /**
     * @var array
     */
    public static $js = [];

    /**
     * @var Extension[]
     */
    protected static $extensions = [];

    /**
     * @var []Closure
     */
    public static $booting;

    /**
     * @var []Closure
     */
    public static $booted;

    /**
     * @var array
     */
    protected static $primaryKeyMap = [];

    /**
     * @var bool
     */
    private static $loadedEnv;

    /**
     * Returns the long version of Laravel-admin.
     *
     * @return string The long application version
     */
    public static function getLongVersion()
    {
        return sprintf('Swoft-admin <comment>version</comment> <info>%s</info>', self::VERSION);
    }

    /**
     *
     * @param Closure $callable
     * @return \Swoft\Admin\Grid
     */
    public static function grid(Closure $callable = null)
    {
        return new Grid($callable);
    }

    /**
     *
     * @param Closure $callable
     * @return \Swoft\Admin\Form
     */
    public static function form(Closure $callable = null)
    {
        return new Form($callable);
    }

    /**
     * Build show page.
     *
     * @param $id
     * @param mixed $callable
     *
     * @return Show
     */
    public static function show($id, $callable = null)
    {
        return new Show($id, $callable);
    }

    /**
     * @param Closure $callable
     *
     * @return \Swoft\Admin\Layout\Content
     */
    public static function content(Closure $callable = null)
    {
        return new Content($callable);
    }

    /**
     * @return Url
     */
    public static function url()
    {
        if ($url = static::getContextAttribute('__url__')) {
            return $url;
        }
        $url = Url::make();
        static::setContextAttribute('__url__', $url);

        return $url;
    }

    /**
     * @param $model
     *
     * @return Model
     */
    public static function getModel($model)
    {
        if ($model instanceof Model) {
            return $model;
        }

        if (is_string($model) && class_exists($model)) {
            return static::getModel(new $model());
        }

        throw new InvalidArgumentException("$model is not a valid model");
    }

    /**
     * 获取控制器名称
     *
     * @return string
     */
    public static function getControllerName()
    {
        $controllerClass = static::getContextAttribute('__controller__') ?:
            RequestContext::getContextDataByKey('controllerClass');

        if (!$controllerClass) {
            return '';
        }
        $controllerClass = explode("\\", $controllerClass);
        $controller = str_replace('Controller', '', end($controllerClass));

        return str__slug($controller);
    }

    /**
     * 更改控制器名称
     * 此控制器名称主要用于生成url, 以及字段语言包翻译
     *
     * @param string $name
     */
    public static function setControllerName(string $name)
    {
        static::setContextAttribute('__controller__', $name);
    }

    /**
     * 设置url前缀
     *
     * @param string $prefix
     */
    public static function setUrlPrefix(string $prefix)
    {
        static::setContextAttribute('__url__prefix__', $prefix);
    }

    /**
     * 判断是否是debug环境(此方法在任何阶段都可用,包括before start)
     *
     * @return bool
     */
    public static function isDebug(): bool
    {
        try {
            if (App::hasBean('config')) {
                return (bool) config('debug');
            }
        } catch (\Throwable $e) {
        }

        static::loadEnv();

        $file = App::getAlias('@root/config/properties/app.php');
        if (!is_file($file)) {
            return false;
        }
        return (bool)array_get((array)include $file, 'debug');
    }

    /**
     * 如果没有加载环境配置,则自动加载
     */
    private static function loadEnv()
    {
        if (static::$loadedEnv) {
            return;
        }
        if (env('APP_DEBUG') === null) {
            $file = '.env';
            $filePath = App::getAlias('@root') . DS . $file;
            if (\file_exists($filePath) && \is_readable($filePath)) {
                (new Dotenv(App::getAlias('@root'), $file))->load();
            }
        }
        static::$loadedEnv = true;
    }

    /**
     * 翻译数据表字段
     *
     * @param string|array $column
     * @return mixed
     */
    public static function translateField($column)
    {
        if (!is_string($column)) {
            return $column;
        }

        $controllerSlug = Admin::getControllerName();

        return str_replace(['.', '_'], ' ', t($column, "$controllerSlug.fields"));
    }

    /**
     * 翻译labels分类下的字段
     *
     * @param string|array $column
     * @return mixed
     */
    public static function translateLabel($column)
    {
        if (!is_string($column)) {
            return $column;
        }

        $controllerSlug = Admin::getControllerName();

        return str_replace(['.', '_'], ' ', t($column, "$controllerSlug.labels"));
    }

    /**
     * 获取主键键名
     *
     * @param Model|string $model
     * @return string
     */
    public static function getPrimaryKeyName($model)
    {
        $className = $model instanceof Model ? get_class($model) : $model;
        if (isset(self::$primaryKeyMap[$className])) {
            return self::$primaryKeyMap[$className];
        }

        $entities   = EntityCollector::getCollector();
        $fields     = $entities[$className]['field'];
        $idProperty = $entities[$className]['table']['id'];

        return self::$primaryKeyMap[$className] = $fields[$idProperty]['column'];
    }


    /**
     * Add css or get all css.
     *
     * @param string|array $css
     *
     * @return string|void
     */
    public static function css($css = null)
    {
        $tid = Coroutine::tid();
        if (!isset(self::$css[$tid])) {
            self::$css[$tid] = [];
        }

        if (!is_null($css)) {
            self::$css[$tid] = array_merge(self::$css[$tid], (array) $css);
            return;
        }


        $css = [];
        foreach (array_unique(self::$css[$tid]) as &$v) {
            $css[] = html_css($v);
        }

        unset(self::$css[$tid]);

        return join('', $css);
    }

    /**
     * Add js or get all js.
     *
     * @param null $js
     *
     * @return string|void
     */
    public static function js($js = null)
    {
        $tid = Coroutine::tid();
        if (!isset(self::$js[$tid])) {
            self::$js[$tid] = [];
        }

        if (!is_null($js)) {
            // 标记有新js
            static::setContextAttribute('has_new_js', 1);
            self::$js[$tid] = array_merge(self::$js[$tid], (array) $js);
            return;
        }

        $js = [];
        foreach (array_unique(self::$js[$tid]) as &$v) {
            $js[] = html_js($v);
        }

        unset(self::$js[$tid]);

        return join('', $js);
    }

    /**
     * 加载js代码
     *
     * @param string $script
     * @return void|string
     */
    public static function script(string $script = null)
    {
        $tid = Coroutine::tid();
        if (!isset(self::$script[$tid])) {
            self::$script[$tid] = [];
        }

        if ($script !== null) {
            self::$script[$tid][] = &$script;

            return;
        }

        $scripts = [];
        foreach (array_unique(self::$script[$tid]) as &$v) {
            $scripts[] = $v;
        }

        unset(self::$script[$tid]);

        if (!$scripts) {
            return;
        }

        if (static::getContextAttribute('has_new_js') && is_pjax_request()) {
            // 如果是pjax请求, 需要等所有js脚本加载完成之后才能执行js代码
            // 此处必须是使用one方法绑定事件监听函数, 否则会多次执行
            return '<script>$(document).one("pjax:script",function(){'.join(';', $scripts).'});</script>';
        }

        return '<script>$(function(){'.join(';', $scripts).'});</script>';
    }

    /**
     * Left sider-bar menu.
     *
     * @return array
     */
    public static function menu()
    {
        return (new Menu())->toTree();
    }

    /**
     * Get admin title.
     *
     * @return string
     */
    public static function title()
    {
        return config('admin.title');
    }

    /**
     * Get current login user.
     *
     * @return Model
     */
    public static function user()
    {
        return static::getContextAttribute('__user__');
    }

    /**
     * 设置登陆用户
     *
     * @param Model $user
     */
    public static function setUser(Model $user)
    {
        static::setContextAttribute('__user__', $user);
    }

    /**
     * 释放资源
     */
    public static function release()
    {
        $tid = Coroutine::tid();
        unset(
            self::$script[$tid],
            self::$js[$tid],
            self::$css[$tid],
            static::$attributes[$tid]
        );

        Form::release();
    }

    /**
     * 可以通过此对象动态的往头部导航栏添加内容
     * 只对当前请求有效
     * 如果需要添加全局生效的内容到导航栏
     * 请调用setNavbarView方法设置自定义导航栏模板
     *
     * @param Closure $builder
     */
    public static function navbar(Closure $builder)
    {
        call_user_func($builder, static::getNavbar());
    }

    /**
     * Get navbar object.
     *
     * @return Navbar
     */
    public static function getNavbar()
    {
        if (!$navbar = static::getContextAttribute('__navvar__')) {
            $navbar = new Navbar();
            static::setContextAttribute('__navbar__', $navbar);
        }

        return $navbar;
    }

    /**
     * 缓存当前请求数据
     *
     * @param string|array $key
     * @param mixed $value
     */
    public static function setContextAttribute($key, $value = null)
    {
        $tid = Coroutine::tid();
        if (!isset(static::$attributes[$tid])) {
            static::$attributes[$tid] = [];
        }

        if (is_array($key)) {
            static::$attributes[$tid] = array_merge(static::$attributes[$tid], $key);
            return;
        }
        static::$attributes[$tid][$key] = $value;
    }

    /**
     *
     * @param string|int $key
     * @return bool
     */
    public static function hasContextAttribute($key)
    {
        $tid = Coroutine::tid();
        return isset(static::$attributes[$tid][$key]);
    }

    /**
     * @param $key
     */
    public static function deleteContextAttribute($key)
    {
        $tid = Coroutine::tid();
        if (isset(static::$attributes[$tid][$key])) {
            unset(static::$attributes[$tid][$key]);
        }
    }

    /**
     * @param $key
     * @param mixed $default
     * @return mixed
     */
    public static function getContextAttribute($key, $default = null)
    {
        $tid = Coroutine::tid();
        if (!isset(static::$attributes)) {
            static::$attributes[$tid] = [];
        }
        return isset(static::$attributes[$tid][$key]) ? static::$attributes[$tid][$key] : $default;
    }


    /**
     * Extend a extension.
     *
     * @param string $name
     * @param Extension $extension
     */
    public static function extend($name, Extension $extension)
    {
        static::$extensions[$name] = $extension;
    }

    /**
     * 获取所有扩展对象
     *
     * @return Extension[]
     */
    public static function getExtensions()
    {
        return static::$extensions;
    }

    /**
     * 获取扩展类
     *
     * @return array
     */
    public static function getExtenstionClass()
    {
        $classes = [];
        foreach ((array)config('admin.extensions') as $v) {
            if (is_string($v) && class_exists($v)) {
                $classes[] = $v;
            }
        }
        return $classes;
    }

    /**
     * @param callable $callback
     */
    public static function booting(callable $callback)
    {
        static::$booting[] = $callback;
    }

    /**
     * @param callable $callback
     */
    public static function booted(callable $callback)
    {
        static::$booted[] = $callback;
    }

}
