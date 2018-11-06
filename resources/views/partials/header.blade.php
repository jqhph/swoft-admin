<!-- Main Header -->
<header class="main-header">

    <!-- Logo -->
    <a href="{{ admin_base_path() }}" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini">{!! config('admin.logo-mini', config('admin.name')) !!}</span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg">{!! config('admin.logo', config('admin.name')) !!}</span>
    </a>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only"></span>
        </a>

        {!! \Swoft\Admin\Admin::getNavbar()->render('left') !!}

        <!-- Navbar Right Menu -->
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">

                {!! \Swoft\Admin\Admin::getNavbar()->render() !!}

                <!-- User Account Menu -->
                {{--<li class="dropdown user user-menu">--}}
                    {{--<!-- Menu Toggle Button -->--}}
                    {{--<a href="#" class="dropdown-toggle" data-toggle="dropdown">--}}
                        {{--<!-- The user image in the navbar-->--}}
                        {{--<img src="" class="user-image" alt="User Image">--}}
                        {{--<!-- hidden-xs hides the username on small devices so only the image appears. -->--}}
                        {{--<span class="hidden-xs">test</span>--}}
                    {{--</a>--}}
                    {{--<ul class="dropdown-menu">--}}
                        {{--<!-- The user image in the menu -->--}}
                        {{--<li class="user-header">--}}
                            {{--<img src="" class="img-circle" alt="User Image">--}}
                            {{--<p>--}}
                                {{--test--}}
                                {{--<small>Member since admin 2018-09-11</small>--}}
                            {{--</p>--}}
                        {{--</li>--}}
                        {{--<li class="user-footer">--}}
                            {{--<div class="pull-left">--}}
                                {{--<a href="{{ admin_base_path('auth/setting') }}" class="btn btn-default btn-flat">{{ t('Setting', 'admin') }}</a>--}}
                            {{--</div>--}}
                            {{--<div class="pull-right">--}}
                                {{--<a href="{{ admin_base_path('auth/logout') }}" class="btn btn-default btn-flat">{{ t('Logout', 'admin') }}</a>--}}
                            {{--</div>--}}
                        {{--</li>--}}
                    {{--</ul>--}}
                {{--</li>--}}
                <!-- Control Sidebar Toggle Button -->
                {{--<li>--}}
                    {{--<a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>--}}
                {{--</li>--}}
            </ul>
        </div>
    </nav>
</header>