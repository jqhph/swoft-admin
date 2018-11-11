<div class="{{$viewClass['form-group']}} {!! !$errors->has($column) ?: 'has-error' !!}">
    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>
    <div class="{{$viewClass['field']}}">
        @include('admin::form.error')
        <div class="input-group">
            @if ($prepend)
                <span class="input-group-addon">{!! $prepend !!}</span>
            @endif
            <input {!! $attributes !!} />
        </div>
        @include('admin::form.help-block')
    </div>
</div>