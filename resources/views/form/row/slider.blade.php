<div class="{{$viewClass['form-group']}} {!! !$errors->has($column) ?: 'has-error' !!}">
    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>
    <div class="{{$viewClass['field']}}">
        @include('admin::form.error')
        <input type="text" class="{{$class}}" name="{{$name}}" data-from="{{ old_input($column, $value) }}" {!! $attributes !!} />
        @include('admin::form.help-block')

    </div>
</div>
