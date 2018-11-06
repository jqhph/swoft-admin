<?php

return [
    /**
    | 开启web debug控制台
    | 此参数只在 swoft debug 环境下有效
    |
     */
    'debug-console' => true,

    /*
     | 模板文件路径，需要配置完整路径，或别名
     | 默认使用内置模板
     */
    'views-path' => '@res/views/swoft-admin',

    /*
    | 静态资源文件路径，需要配置可访问url路径或完整域名。
    | 默认"/assets/swoft-admin"
    */
    'assets-path' => '/assets/swoft-admin',

    /*
    | 网站名称
    |
    */
    'name' => 'Swoft Admin',

    /*
    | 网站logo
    | `img` tag, eg '<img src="http://logo-url" alt="Admin logo">'.
    |
    */
    'logo' => '<b>Swoft</b> Admin',

    /*
    | 菜单栏缩小后logo
    */
    'logo-mini' => '<b>Sa</b>',

    /*
    | 路由前缀配置
    */
    'route' => [
        'prefix' => 'admin',
    ],

    /*
    | 网站title
    */
    'title' => 'Admin',

    /*
     | 是否开启异常处理功能
     | 异常处理功能仅在 debug 模式下有效
     | 生成环境请务必要关闭 debug 模式!!!
     |
     */
    'exception-report' => true,

    /*
     | 是否启用 filp/whoops 插件处理异常信息
     | 此参数在"exception-report"开启的情况下有效
     | 如果把值设置为false, 系统将采用系统内置的错误信息渲染功能展示错误信息
     |
     */
    'use-whoops' => true,

    /*
     | csrf token验证配置
     |
     */
    'csrf' => [
        // 是否开启csrf token验证
        'enable' => true,

        // 配置需要跳过验证的路径
        'except' => [

        ],
    ],

    /*
    | 文件上传配置
    */
    'upload' => [
        // Image and file upload path under the disk above.
        'directory' => [
            'image' => 'images', // 圖片上傳文件夾
            'file'  => 'files', // 文件上傳文件夾
        ],

        /*
         | 本项目使用 league/flysystem 上传文件
         | Supported Drivers: "Local", "Ftp", "Sftp"
         | @see http://flysystem.thephpleague.com/docs/
         */
        'filesystem' => [
            // 文件系统驱动配置
            'local' => [
                // 文件系统驱动配置
                'adapter' => [
                    'class' => League\Flysystem\Adapter\Local::class,
                    // 传入驱动构造方法的参数
                    [
                        alias('@root/public/uploads')
                    ],
                ],
            ],
            'public' => [
                // 文件系统驱动配置
                'adapter' => [
                    'class' => League\Flysystem\Adapter\Local::class,
                    [
                        alias('@root/public/uploads')
                    ],
                ],
                /*
                 | 文件资源可访问路径配置，不配置则不可访问
                 | 使用方法
                 | $filesystem->get('url');
                 */
                'url' => '/uploads', // 文件可访问url配置
            ],
        ],
    ],

    /*
    | Supported: "tencent", "google", "yandex".
    */
    'map_provider' => 'tencent',

    /*
    | 皮肤配置
    | @see https://adminlte.io/docs/2.4/skin
    |
    | Supported:
    |    "skin-blue", "skin-blue-light", "skin-yellow", "skin-yellow-light",
    |    "skin-green", "skin-green-light", "skin-purple", "skin-purple-light",
    |    "skin-red", "skin-red-light", "skin-black", "skin-black-light".
    */
    'skin' => 'skin-blue-light',

    /*
    | 布局配置
    | @see https://adminlte.io/docs/2.4/layout
    |
    | Supported: "fixed", "layout-boxed", "layout-top-nav", "sidebar-collapse",
    | "sidebar-mini".
    */
    'layout' => ['sidebar-mini'],

    /*
    | 页面底部显示版本号
    |
    */
    'show_version' => true,

    /*
    | 页面底部显示环境
    |
    */
    'show_environment' => true,

    /*
    | 扩展配置
    |
    | 配置扩展类名即可,系统会自动加载并初始化拓展
    |
    | Example:
    | MyExtension::class,
    |
    */
    'extensions' => [
    ]
];
