<div class="filter-input col-sm-{{ $width }} " >
    <div class="form-group" >
        <div class="input-group input-group-sm">
            <span class="input-group-addon"><b>{{$label}}</b></span>
            <input type="text" class="form-control" placeholder="{{$label}}" name="{{$name['start']}}" value="{{ http_get($name['start'], array_get($value, 'start')) }}">
            <span class="input-group-addon" style="border-left: 0; border-right: 0;">to</span>
            <input type="text" class="form-control" placeholder="{{$label}}" name="{{$name['end']}}" value="{{ http_get($name['end'], array_get($value, 'end')) }}">
        </div>
    </div>
</div>