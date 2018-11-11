    </div>

    @if ($allowNavbarAndSidebar)
        @include('admin::partials.footer')
     @else
        @if(config('admin.debug-console'))
        @include('admin::debug.content')
        @endif
    @endif
    </div>

    <script>
        function LA() {}
        LA.token = "{{ \Swoft\Support\SessionHelper::wrap() ? \Swoft\Support\SessionHelper::wrap()->token() : '' }}";
    </script>
    {!! html_js('@admin/layer/layer.js') !!}
    {!! html_js('@admin/swoft-admin/main.js') !!}
    </body>
</html>
