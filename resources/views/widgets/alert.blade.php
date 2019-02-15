<div {!! $attributes !!} >
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    @if ($title)
    <h4><i class="icon fa fa-{{ $icon }}"></i> {!! $title !!}</h4>
    @endif
    {!! $content !!}
</div>