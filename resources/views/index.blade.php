@php
    $isPjax = Swoft\Admin\Admin::isPjaxRequest();
@endphp
@if (!$isPjax)
    @include('admin::index-header')
@endif

{!! \Swoft\Admin\Admin::css() !!}
@yield('content')
{!! \Swoft\Admin\Admin::js() !!}
{!! \Swoft\Admin\Admin::script() !!}

@if (!$isPjax)
    @include('admin::index-footer')
@endif