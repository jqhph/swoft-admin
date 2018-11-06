<div class="box-footer">
    <div class="col-md-{{$width['label']}}">
    </div>

    <div class="col-md-{{$width['field']}}">

        @if(in_array('submit', $buttons))
            <div class="btn-group pull-right">
                <button type="submit" class="btn btn-primary">{{ t('Submit', 'admin') }}</button>
            </div>
            @if(in_array('continue_editing', $checkboxes))
            <label class="pull-right" style="margin: 5px 10px 0 0;">
                <input type="checkbox" class="after-submit" name="after-save" value="1"> {{ t('Continue editing', 'admin') }}
            </label>
            @endif

            @if(in_array('view', $checkboxes))
            <label class="pull-right" style="margin: 5px 10px 0 0;">
                <input type="checkbox" class="after-submit" name="after-save" value="2"> {{ t('View', 'admin') }}
            </label>
            @endif
        @endif

        @if(in_array('reset', $buttons))
        <div class="btn-group pull-left">
            <button type="reset" class="btn btn-warning">{{ t('Reset', 'admin') }}</button>
        </div>
        @endif
    </div>
</div>