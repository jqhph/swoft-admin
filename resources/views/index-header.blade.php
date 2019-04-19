<!DOCTYPE html>
<html lang="{{ current_lang() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>{{ \Swoft\Admin\Admin::title() }}</title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

        {!! html_css('@admin/AdminLTE/bootstrap/css/bootstrap.min.css') !!}
        <!-- Font Awesome -->
        {!! html_css('@admin/font-awesome/css/font-awesome.min.css') !!}

        @if ($allowNavbarAndSidebar)
        <!-- Theme style -->
        {!! html_css("@admin/AdminLTE/dist/css/skins/" . config('admin.skin') .".min.css") !!}
        @endif

        {!! html_css('@admin/AdminLTE/dist/css/AdminLTE.min.css') !!}
        {!! html_css('@admin/swoft-admin/main.min.css') !!}

        {!! html_js('@admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js') !!}
        {!! html_js('@admin/AdminLTE/bootstrap/js/bootstrap.min.js') !!}
        {!! html_js('@admin/AdminLTE/plugins/slimScroll/jquery.slimscroll.min.js') !!}

        {!! html_js('@admin/AdminLTE/dist/js/app.min.js') !!}

        {!! html_js('@admin/jquery-pjax/jquery.pjax.min.js') !!}

        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        @if (!$allowNavbarAndSidebar)
            {{-- 当不需要菜单时隐藏菜单栏 --}}
            <style>.content-wrapper,.sidebar-mini.sidebar-collapse .content-wrapper{margin-left:0!important}</style>
        @endif
</head>
<script>
    function LA() {}
    LA.token = "{{ \Swoft\Support\SessionHelper::wrap() ? \Swoft\Support\SessionHelper::wrap()->token() : '' }}";

    /**
     * 监听dom加载完成事件
     * 此方法会自动监听iframe窗口pjax加载js脚本完毕事件
     *
     * @param callback
     * @param {window} win iframe窗口window对象, 监听iframe窗dom加载完成事件
     * @returns  {*}
     */
    LA.ready = function (callback, win) {
        if (!win || win === window) {
            if (typeof LA.pjaxresponse == 'undefined') {
                return $(callback, win || window);
            }
            return $(document, win || window).one('pjax:script', callback);
        }

        var proxy = function (e) {
            win.$(win.$('#pjax-container')).one('pjax:script', proxy);

            callback(e);
        };

        win.LA.ready(proxy);
    };
</script>
<body class="swoft-admin-body hold-transition {{config('admin.skin')}} {{join(' ', config('admin.layout'))}}">
<div class="wrapper">

    @if ($allowNavbarAndSidebar)
        {!! Swoft\Admin\Admin::getNavbar()->render() !!}
        @include('admin::partials.sidebar')
    @endif

    <div class="content-wrapper" id="pjax-container">
