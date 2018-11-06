<div class="form-horizontal">
    <div class="fields-group">
        @foreach($fields as $field)
            {!! $field->render() !!}
        @endforeach
        <div class="clearfix"></div>
    </div>
</div>
