# swoft-admin

`Swoft Admin`是基于`swoft`框架开发而成的后台系统快速构建工具，使用极少的代码即可构建出一个功能完善的后台系统，性能强悍、代码简洁、易扩展。

>本项目基于[laravel-admin](http://laravel-admin.org/)重构而成，保留了基本的代码架构和大部分api。主要改动如下：
>+ 分离了页面构建层和数据层（移除了对框架orm的强绑定）
>+ 解决了`pjax`按需加载问题 
>+ 调整了部分api的用法

[Demo](http://103.45.104.52:8000)|[文档]()

## 环境
+ PHP 7.0 +
+ Swoft 1.0.0最新版本
+ Swoole 推荐4.0以上版本

## 特性

+ 基于[swoole](https://www.swoole.com/)，程序常驻内存性能强悍，支持协程（异步IO同步代码）
+ `Admin::grid`支持快速构建数据表格
+ `Admin::form`支持快速构建数据表单
+ 支持代码生成器快速生成CURD代码、语言包、数据库迁移文件、SWOFT实体等
+ 支持`Blade`模板引擎, 支持使用路径别名引入静态资源
+ 支持`pjax`按需加载构建单页应用
+ 支持web debug控制台
+ 支持扩展组件，支持插件机制
+ 支持数据库版本迁移管理
+ 基于[league/flysystem](http://flysystem.thephpleague.com/docs/)上传文件，可以轻松实现远程上传及云服务上传

## Demo

[Demo](http://103.45.104.52:8000)


## 后端依赖组件

+ [Swoft](http://www.swoft.org/)
+ [league/flysystem](http://flysystem.thephpleague.com/docs/)
+ [phinx](http://docs.phinx.org/en/latest/)
+ [filp/whoops](http://filp.github.io/whoops/)


## 前端依赖组件
> 注意，为解决前端pjax按需加载功能，本项目修改了`pjax`的代码，请勿升级
> 同样，为优化按钮布局，`RWD-Table-Patterns`的代码也进行了微调，请勿升级
+ [AdminLTE](https://almsaeedstudio.com/)
+ [RWD-Table-Patterns](http://gergeo.se/RWD-Table-Patterns/)
+ [Datetimepicker](http://eonasdan.github.io/bootstrap-datetimepicker/)
+ [font-awesome](http://fontawesome.io)
+ [moment](http://momentjs.com/)
+ [Google map](https://www.google.com/maps)
+ [Tencent map](http://lbs.qq.com/)
+ [bootstrap-fileinput](https://github.com/kartik-v/bootstrap-fileinput)
+ [jquery-pjax](https://github.com/defunkt/jquery-pjax)
+ [Nestable](http://dbushell.github.io/Nestable/)
+ [layer弹出层](https://layer.layui.com/)
+ [editor.md](https://pandao.github.io/editor.md/)
+ [bootstrap-number-input](https://github.com/wpic/bootstrap-number-input)
+ [fontawesome-iconpicker](https://github.com/itsjavi/fontawesome-iconpicker)

## 安装
```
composer require lldca/swoft-admin
```


确保拥有 `@root/config/properties`、`@root/resources`、`@root/public`目录的写权限，然后运行

```bash
php bin/swoft admin:install
```
运行完命令打开 `@root/config/properties/app.php`配置文件，检查文件末尾是否增加了以下配置，如缺少其中某些字段，手动增加即可。
```php
   'components' => [
        'custom' => [
            'Swoft\Admin',
            'Swoft\Blade'
        ],
    ],        
    /*
     | 静态资源帮助工具配置
     |
     */
    'assets' => [
        // 静态资源域名配置
        'resource-server' => env('ASSETS_SERVER'),

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
    ],
    
    'admin' => require __DIR__ . '/admin.php',
```

以上全部完成后需要进入`@root/config/beans/base.php`中加入如下配置开启session功能方可正常使用:
```
'serverDispatcher' => [
    'middlewares' => [
         Swoft\Session\Middleware\SessionMiddleware::class,
    ]
],
'sessionManager' => [
    'class' => \Swoft\Session\SessionManager::class,
    'config' => [
        'driver' => 'file',
        'name' => 'SWOFT_SESSION_ID',
        'lifetime' => 1800,
        'expire_on_close' => false,
        'encrypt' => false,
        'storage' => '@runtime/sessions',
    ],
],
```


## 加入我们
如果你对此项目有兴趣，欢迎加入我们。
欢迎大家提建议和pr。

