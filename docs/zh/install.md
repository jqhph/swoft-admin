
```
composer require lldca/swoft-admin
```
然后打开`@root/config/properties/app.php`文件，加上注解扫描配置
```php
 'bootScan'     => [
    // 必须加上 
    'Swoft\Admin\Controllers',
    'Swoft\Admin\Bootstrap',
    'Swoft\Admin\Console',
],
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
