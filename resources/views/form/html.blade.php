<div class="{{$viewClass['form-group']}} {!! !$errors->has($column) ?: 'has-error' !!}">
    <label  class="{{$viewClass['label']}} control-label">{{$label}}</label>
    <div class="{{$viewClass['field']}}">
        <div class="input-group">
            {!! $html !!}
        </div>
        @include('admin::form.help-block')
    </div>
</div>