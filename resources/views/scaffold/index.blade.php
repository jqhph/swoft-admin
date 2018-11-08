<form method="post" action="{{$action}}" id="scaffold" pjax-container>
    <input type="hidden" name="_token" value="{{session()->token()}}"/>
    <div class="box-body">

        <div class="form-horizontal">

            <div class="form-group">

                <label for="inputTableName" class="col-sm-1 control-label">
                    {{ t('Table name', 'admin') }}
                </label>

                <div class="col-sm-4">
                    <input type="text" name="table_name" class="form-control" id="inputTableName" placeholder="Table name" value="{{ old_input('table_name') }}">
                </div>

                <span class="help-block hide" id="table-name-help">
                <i class="fa fa-info"></i>&nbsp; {{ t('Table name can\'t be empty!', 'admin.scaffold') }}
            </span>
            </div>

            <div class="form-group">
                <label for="inputPrefix" class="col-sm-1 control-label">
                    {{ t('Table prefix', 'admin') }}
                </label>

                <div class="col-sm-4">
                    <input type="text" name="table_prefix" class="form-control" id="inputPrefix" placeholder="Table prefix" value="{{ old_input('table_prefix') }}">
                </div>
            </div>

            <div class="form-group">
                <label for="inputControllerName" class="col-sm-1 control-label">{{ t('Controller', 'admin') }}</label>

                <div class="col-sm-4">
                    <input type="text" name="controller_name" class="form-control" id="inputControllerName" placeholder="Controller" value="{{ old_input('controller_name', $controllerNamespace) }}">
                </div>
            </div>

            <div class="form-group">
                <label for="inputModelName" class="col-sm-1 control-label">
                    {{ t('Model', 'admin') }}
                </label>

                <div class="col-sm-4">
                    <input type="text" name="model_name" class="form-control" id="inputModelName" placeholder="Model" value="{{ old_input('model_name', $modelNamespace) }}">
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-11">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" checked value="migration" name="create[]" />
                            {{ t('Create migration', 'admin.scaffold') }}
                        </label>
                        <label>
                            <input type="checkbox" checked value="model" name="create[]" />
                            {{ t('Create model', 'admin.scaffold') }}
                        </label>
                        <label>
                            <input type="checkbox" checked value="controller" name="create[]" />
                            {{ t('Create controller', 'admin.scaffold') }}
                        </label>
                        <label>
                            <input type="checkbox" value="migrate" name="create[]" />
                            {{ t('Run migrate', 'admin.scaffold') }}
                        </label>
                        <label>
                            <input type="checkbox" checked value="lang" name="create[]" />
                            {{ t('Create language-pack', 'admin.scaffold') }}
                            <i title="{{ t('lang_tip', 'admin.scaffold') }}" class="fa fa-info-circle" data-toggle="tooltip" data-placement="top"></i>

                        </label>
                    </div>
                </div>
            </div>

        </div>

        <hr />

        <h4>
            {{ t('Fields', 'admin') }}
            <i title="{{ t('field_tip', 'admin.scaffold') }}" class="fa fa-info-circle" data-toggle="tooltip" data-placement="top"></i>
        </h4>

        <table class="table table-hover" id="table-fields">
            <tbody>
            <tr>
                <th style="width: 200px">{{ t('Field name', 'admin') }}</th>
                <th>{{ t('Translation', 'admin') }}</th>
                <th>{{ t('Type', 'admin') }}</th>
                <th>{{ t('Nullable', 'admin') }}</th>
                <th>{{ t('Key', 'admin.scaffold') }}</th>
                <th>{{ t('Default value', 'admin') }}</th>
                <th>{{ t('Comment', 'admin.scaffold') }}</th>
                <th>{{ t('Action', 'admin') }}</th>
            </tr>

            @if(old_input('fields'))
                @foreach(old_input('fields') as $index => $field)
                    <tr>
                        <td>
                            <input type="text" name="fields[{{$index}}][name]" class="form-control" placeholder="Field name" value="{{$field['name']}}" />
                        </td>
                        <td>
                            <input type="text" name="fields[{{$index}}][lang]" class="form-control" placeholder="Translation" value="{{$field['lang']}}" />
                        </td>
                        <td>
                            <select style="width: 200px" name="fields[{{$index}}][type]">
                                @foreach($dbTypes as $k => $types)
                                    <optgroup label="{{ $k }}">
                                        @foreach ($types as $type)
                                            <option {{$field['type'] == $type ? 'selected' : '' }} value="{{ $type }}">{{ucfirst($type)}}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="checkbox" name="fields[{{$index}}][nullable]" {{ array_get($field, 'nullable') == 'on' ? 'checked': '' }}/></td>
                        <td>
                            <select style="width: 150px" name="fields[{{$index}}][key]">
                                {{--<option value="primary">Primary</option>--}}
                                <option value="" {{$field['key'] == '' ? 'selected' : '' }}>NULL</option>
                                <option value="unique" {{$field['key'] == 'unique' ? 'selected' : '' }}>Unique</option>
                                <option value="index" {{$field['key'] == 'index' ? 'selected' : '' }}>Index</option>
                            </select>
                        </td>
                        <td><input type="text" class="form-control" placeholder="default value" name="fields[{{$index}}][default]" value="{{$field['default']}}"/></td>
                        <td><input type="text" class="form-control" placeholder="comment" name="fields[{{$index}}][comment]" value="{{$field['comment']}}" /></td>
                        <td><a class="btn btn-sm btn-danger table-field-remove"><i class="fa fa-trash"></i></a></td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td>
                        <input type="text" name="fields[0][name]" class="form-control" placeholder="Field name" />
                    </td>
                    <td>
                        <input type="text" name="fields[0][lang]" class="form-control" placeholder="Translation" />
                    </td>
                    <td>
                        <select class="form-control" style="width: 200px" name="fields[0][type]">
                            @foreach($dbTypes as $k => $types)
                                <optgroup label="{{ $k }}">
                                    @foreach ($types as $type)
                                    <option value="{{ $type }}">{{ucfirst($type)}}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="checkbox" name="fields[0][nullable]" /></td>
                    <td>
                        <select class="form-control" style="width: 150px" name="fields[0][key]">
                            {{--<option value="primary">Primary</option>--}}
                            <option value="" selected>NULL</option>
                            <option value="unique">Unique</option>
                            <option value="index">Index</option>
                        </select>
                    </td>
                    <td><input type="text" class="form-control" placeholder="Default value" name="fields[0][default]"></td>
                    <td><input type="text" class="form-control" placeholder="Comment" name="fields[0][comment]"></td>
                    <td><a class="btn btn-sm btn-danger table-field-remove"><i class="fa fa-trash"></i> </a></td>
                </tr>
            @endif
            </tbody>
        </table>

        <hr style="margin-top: 0;"/>

        <div class='form-inline margin' style="width: 100%">


            <div class='form-group'>
                <button type="button" class="btn btn-sm btn-success" id="add-table-field"><i class="fa fa-plus"></i>&nbsp;&nbsp;
                    {{ t('Add field', 'admin') }}
                </button>
            </div>

            <div class='form-group pull-right' style="margin-right: 20px; margin-top: 5px;">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" checked name="timestamps"> Created_at & Updated_at
                    </label>
                    &nbsp;&nbsp;
                    <label>
                        <input type="checkbox" name="soft_deletes"> Soft deletes
                    </label>

                </div>
            </div>

            <div class="form-group pull-right" style="margin-right: 20px;">
                <i title="{{ t('primarykey_tip', 'admin.scaffold') }}" class="fa fa-info-circle" data-toggle="tooltip" data-placement="top"></i>
                <label for="inputPrimaryKey">{{ t('Primary key', 'admin.scaffold') }}</label>
                <input type="text" name="primary_key" class="form-control" id="inputPrimaryKey" placeholder="Primary key" value="id" style="width: 100px;">
            </div>

        </div>
        <br>

        <input type="hidden" name="preview" value=""/>
    </div>
    <!-- /.box-body -->
    <div class="box-footer">
        <div class="btn-group pull-right">
            <a class="btn btn-purple preview ">{{ t('Preview Controller', 'admin') }}</a>
            <button type="submit" class="btn btn-info ">{{ t('Submit', 'admin') }}</button>
        </div>
    </div>

<!-- /.box-footer -->
</form>


<template id="table-field-tpl">
    <tr>
        <td>
            <input type="text" name="fields[__index__][name]" class="form-control" placeholder="Field name" />
        </td>
        <td>
            <input type="text" name="fields[__index__][lang]" class="form-control" placeholder="Translation" />
        </td>
        <td>
            <select class="form-control" style="width: 200px" name="fields[__index__][type]">
                @foreach($dbTypes as $k => $types)
                    <optgroup label="{{ $k }}">
                        @foreach ($types as $type)
                            <option value="{{ $type }}">{{ucfirst($type)}}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </td>
        <td><input type="checkbox" name="fields[__index__][nullable]" /></td>
        <td>
            <select class="form-control" style="width: 150px" name="fields[__index__][key]">
                <option value="" selected>NULL</option>
                <option value="unique">Unique</option>
                <option value="index">Index</option>
            </select>
        </td>
        <td><input type="text" class="form-control" placeholder="default value" name="fields[__index__][default]"></td>
        <td><input type="text" class="form-control" placeholder="comment" name="fields[__index__][comment]"></td>
        <td><a class="btn btn-sm btn-danger table-field-remove"><i class="fa fa-trash"></i></a></td>
    </tr>
</template>
<template id="preview-container">
    <div class="row">{!! $code !!}</div>
</template>
{!! html_css('@admin/AdminLTE/plugins/iCheck/all.css') !!}
{!! html_css('@admin/AdminLTE/plugins/select2/select2.min.css') !!}
{!! html_js('@admin/AdminLTE/plugins/iCheck/icheck.min.js') !!}
{!! html_js('@admin/AdminLTE/plugins/select2/select2.full.min.js') !!}
<script>
    @if(is_pjax_request())
        $(document).one('pjax:script', scaffold_init);
    @else
        $(scaffold_init);
    @endif
    function scaffold_init() {
        @if ($code)
        layer.open({
            type: 1,
            title: '{{ t('Preview', 'admin') }}',
            shadeClose: true,
            shade: false,
            area: ['70%', '80%'],
            content: $('#preview-container').html()
        });
        setTimeout(function () {
            editormd.markdownToHTML('{{ $codeId }}')
        }, 20);
                @endif

        var $inputPreview = $('input[name="preview"]'), $form = $('#scaffold');
        $('.preview').click(function () {
            $inputPreview.val(1);
            $form.submit();
        });

        $('input[type=checkbox]').iCheck({checkboxClass:'icheckbox_minimal-blue'});
        $('select').select2();

        $('#add-table-field').click(function (event) {
            $('#table-fields tbody').append($('#table-field-tpl').html().replace(/__index__/g, $('#table-fields tr').length - 1));
            $('select').select2();
            $('input[type=checkbox]').iCheck({checkboxClass:'icheckbox_minimal-blue'});
        });

        $('#table-fields').on('click', '.table-field-remove', function(event) {
            $(event.target).closest('tr').remove();
        });

        $form.on('submit', function (event) {
            //event.preventDefault();
            if ($('#inputTableName').val() == '') {
                $('#inputTableName').closest('.form-group').addClass('has-error');
                $('#table-name-help').removeClass('hide');
                $inputPreview.val(0);
                return false;
            }

            return true;
        });

        var typing = 0, $model = $('#inputModelName'), $controller = $('#inputControllerName');
        var modelNamespace = 'App\\Models\\Entity\\', controllerNamespace = 'App\\Controllers\\Admin\\';
        $('#inputTableName').on('keyup', function (e) {
            var $this = $(this);
            $this.val(to_line($this.val()));

            if (typing == 1) {
                return;
            }
            typing = 1;

            setTimeout(function () {
                typing = 0;

                write_controller();
                write_model();
            }, 100);

            function write_controller() {
                var val = ucfirst(to_hump($this.val()));
                $controller.val(val ? (controllerNamespace + val + 'Controller') : controllerNamespace);
            }
            function write_model() {
                $model.val(modelNamespace + ucfirst(to_hump($this.val())));
            }

        });

        $('#inputPrefix').on('keyup', function (e) {
            var $this = $(this);
            $this.val(to_line($this.val()));
        });

        // 下划线转换驼峰
        function to_hump(name) {
            return name.replace(/\_(\w)/g, function (all, letter) {
                return letter.toUpperCase();
            });
        }

        // 驼峰转换下划线
        function to_line(name) {
            return name.replace(/([A-Z])/g,"_$1").toLowerCase();
        }

        function ucfirst(str) {
            var reg = /\b(\w)|\s(\w)/g;
            return str.replace(reg,function(m){
                return m.toUpperCase()
            });
        }
    }

</script>