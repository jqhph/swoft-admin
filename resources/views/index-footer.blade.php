    </div>

    @if ($allowNavbarAndSidebar)
        @include('admin::partials.footer')
     @else
        @if(config('admin.debug-console') && \Swoft\Admin\Admin::isDebug())
        @include('admin::debug.content')
        @endif
    @endif
    </div>

    {!! html_js('@admin/layer/layer.js') !!}
    {!! html_js('@admin/swoft-admin/main.js') !!}
    </body>
</html>
