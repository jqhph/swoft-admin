<div {!! $attributes !!}>
    @if ($title || $tools)
    <div class="card-header with-border">
        <h4 style="display:inline">{!! $title !!}</h4>
        <div class="box-tools pull-right">
            @foreach($tools as $tool)
                {!! $tool !!}
            @endforeach
        </div>
        <div style="clear:both;"></div>
        @if ($divider)
        <div class="divider"></div>
        @endif
    </div>
    @endif
    <div class="card-body card-padding panel-collapse collapse" style="display: block;">
        {!! $content !!}
    </div>
</div>