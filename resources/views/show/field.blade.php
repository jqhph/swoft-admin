<div class="line col-sm-{{ $width }} show-field">
    <div class="text">{{ $label }}</div>
        @if($wrapped)
            <div class="box box-solid box-default no-margin box-show">
                <div class="box-body">
                    {!! $content !!}&nbsp;
                </div>
            </div>
        @else
            {!! $content !!}
        @endif
</div>