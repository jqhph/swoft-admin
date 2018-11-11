<div class="{{$viewClass['form-group']}} {!! !$errors->has($column) ?: 'has-error' !!}">
    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>
    <div class="{{$viewClass['field']}}">
        @include('admin::form.error')

        <div class="form-control {{$class}}" name="{{$name}}" placeholder="{{ $placeholder }}" {!! $attributes !!} >
            {{ old_input($column, $value) }}
        </div>
        @include('admin::form.help-block')

    </div>
</div>
