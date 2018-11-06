<div class="row">
    @foreach($fields as $field)
        @if ($field instanceof \Swoft\Admin\Form\Field\Hidden)
        {!! $field->render() !!}
        @else
        <div class="col-md-{{ $field->getWidth()['row'] }} form-row">
            {!! $field->render() !!}
        </div>
        @endif
    @endforeach
</div>