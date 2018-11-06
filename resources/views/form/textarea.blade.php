<div class="{{$viewClass['form-group']}} {!! !$errors->has($column) ?: 'has-error' !!}">
    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>
    <div class="{{$viewClass['field']}}">
        @include('admin::form.error')
        <textarea name="{{$name}}" class="form-control {{$class}}" rows="{{ $rows }}" placeholder="{{ $placeholder }}" {!! $attributes !!} >{{ old_input($column, $value) }}</textarea>
        @include('admin::form.help-block')
    </div>
</div>
