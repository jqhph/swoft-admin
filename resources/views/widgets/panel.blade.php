<div {!! $attributes !!} role="tablist" aria-multiselectable="true">
    @foreach($items as $item)
        @php
            $id = 'pu'.uniqid();
        @endphp
    <div class="panel panel-{{ $item['style'] }}">
        <div class="panel-heading" role="tab" data-toggle="collapse" href="#{!! $id !!}" >
            <h4 class="panel-title">{!! $item['title'] !!}</h4>
        </div>
        <div id="{!! $id !!}" class="panel-collapse collapse {{ $item['show'] }}" >
            <div class="panel-body">
            {!! $item['content'] !!}
            </div>
        </div>
    </div>
    @endforeach
</div>