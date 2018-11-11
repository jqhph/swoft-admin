<div class="{{$viewClass['form-group']}} {!! !$errors->has($column) ?: 'has-error' !!}">
    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">
        {!! $prepend !!}  {{$label}}
    &nbsp;&nbsp;&nbsp; @include('admin::form.error')
    </label>
    <div class="{{$viewClass['field']}}">
        <div class="input-group">
            <input {!! $attributes !!} />
            @if ($append)
                <span class="input-group-addon clearfix">{!! $append !!}</span>
            @endif
        </div>
        @include('admin::form.help-block')
    </div>
</div>