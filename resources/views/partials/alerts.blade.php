@if ($messages = admin_flash_message())
    @foreach ($messages as $bag)
        @php
            $type = array_get($bag->get('type'), 0);
        @endphp
    <div class="alert alert-{{ $type }} alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon
        @if ($type == 'success')
            icon fa fa-check
        @elseif ($type == 'error')
            fa fa-ban
        @elseif ($type == 'info')
            fa fa-info
        @else
            fa fa-warning
        @endif
"></i>{{ array_get($bag->get('title'), 0) }}</h4>
        <p>{!!  array_get($bag->get('message'), 0) !!}</p>
    </div>
    @endforeach
@endif
@if ($errors = get_flash_errors())
    @if ($errors->hasBag('error'))
        <div class="alert alert-danger alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            @foreach($errors->getBag("error")->toArray() as $message)
                <p>{!!  array_get($message, 0) !!}</p>
            @endforeach
        </div>
    @endif
@endif
