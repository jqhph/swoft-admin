@if($notice = get_admin_notice())
    @php
        $type    = array_get($notice->get('type'), 0, 'success');
        $message = array_get($notice->get('message'), 0, '');
        $offset  = array_get($notice->get('offset'), 0, 't');
    @endphp
    <script>$(function () {LA.{{$type}}('{!! $message !!}','{{ $offset }}');});</script>
@endif