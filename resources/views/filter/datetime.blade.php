<div class="input-group input-group-sm">
    <span class="input-group-addon"><b>{{$label}}</b> &nbsp;<i class="fa fa-calendar"></i></span>
    <input class="form-control" id="{{$id}}" placeholder="{{$label}}" name="{{$name}}" value="{{ http_get($name, $value) }}">
</div>