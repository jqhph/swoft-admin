<div class="input-group input-group-sm">
    <span class="input-group-addon"><b>{{$label}}</b></span>
    <select class="form-control {{ $class }}" name="{{$name}}" style="width: 100%;">
        @if($selectAll)
            <option value="{{\Swoft\Admin\Grid\Filter::$ignoreValue}}">{{t('All', 'admin')}}</option>
        @endif
        @foreach($options as $select => $option)
            <option value="{{$select}}" {{ (string)$select === http_get($name, (string)$value) ?'selected':'' }}>{{$option}}</option>
        @endforeach
    </select>
</div>