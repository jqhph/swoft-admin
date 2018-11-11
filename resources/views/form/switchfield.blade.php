<div class="{{$viewClass['form-group']}} {!! !$errors->has($column) ?: 'has-error' !!}">
    <label for="{{$id}}" class="{{$viewClass['label']}} control-label" style="margin:0 0 13px ">{{$label}}</label>
    <div class="{{$viewClass['field']}}">
        <input name="{{$column}}" type="hidden" value="0" />
        @include('admin::form.error')
        <input class="{{$class}}" {{ old_input($column, $value) ? 'checked' : '' }} {!! $attributes !!} />
        @include('admin::form.help-block')
    </div>
</div>
