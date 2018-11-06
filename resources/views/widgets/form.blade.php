@if ($useFormTag)
<form {!! $attributes !!}>
@endif
    <div class="box-body fields-group">
        @if ($style)
            @include("admin::form.$style")
        @else
            @foreach($fields as $field)
                {!! $field->render() !!}
            @endforeach
        @endif

    </div>

    @if ($method != 'GET')
        <input type="hidden" name="_token" value="{{ session()->token() }}">
    @endif
    
    <!-- /.box-body -->
    @if(count($buttons) > 0)
    <div class="box-footer" style="background:transparent">
        <div class="col-md-2"></div>

        <div class="col-md-8">
            @if(in_array('reset', $buttons))
            <div class="btn-group pull-left">
                <button type="reset" class="btn btn-warning pull-right">{{ t('Reset', 'admin') }}</button>
            </div>
            @endif

            @if(in_array('submit', $buttons))
            <div class="btn-group pull-right">
                <button type="submit" class="btn btn-info pull-right">{{ t('Submit', 'admin') }}</button>
            </div>
            @endif
        </div>
    </div>
    @endif
@if ($useFormTag)
</form>
@endif
