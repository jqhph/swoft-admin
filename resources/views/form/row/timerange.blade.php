<div class="{{$viewClass['form-group']}} {!! ($errors->has($errorKey['start'].'start') || $errors->has($errorKey['end'].'end')) ? 'has-error' : ''  !!}">

    <label for="{{$id['start']}}" class="{{$viewClass['label']}} control-label"><i class="fa fa-clock-o fa-fw"></i> {{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <div class="row" style="max-width:600px">
            <div class="col-lg-6">
                <div class="input-group">
                    <input type="text" name="{{$name['start']}}" value="{{ old_input($column['start'], $value['start']) }}" class="form-control {{$class['start']}}" {!! $attributes !!} />
                </div>
            </div>

            <div class="col-lg-6">
                <div class="input-group">
                    <input style="padding-left:6px" type="text" name="{{$name['end']}}" value="{{ old_input($column['end'], $value['end']) }}" class="form-control {{$class['end']}}" {!! $attributes !!} />
                </div>
            </div>
        </div>

        @include('admin::form.help-block')

    </div>
</div>
