@if (!is_pjax_request())
    @include('admin::index-header')
@endif

{!! \Swoft\Admin\Admin::css() !!}
@yield('content')
{!! \Swoft\Admin\Admin::js() !!}
{!! \Swoft\Admin\Admin::script() !!}

@if (!is_pjax_request())
    @include('admin::index-footer')
@else
    <script>LA.pjaxresponse = 1;</script>
@endif