@extends('admin::index')

@section('content')
    @if ($header || $breadcrumb)
    <section class="content-header">
        @if ($header)
        <h1>
            {{ $header ?: t('Title', 'admin') }}
            <small>{{ $description ?: t('Description', 'admin') }}</small>
        </h1>
        @endif

        <!-- breadcrumb start -->
        @if ($breadcrumb)
        <ol class="breadcrumb" style="margin-right: 30px;">
            <li><a href="{{ admin_url('/') }}"><i class="fa fa-dashboard"></i> {{ t('Home', 'admin') }}</a></li>
            @foreach($breadcrumb as $item)
                @if($loop->last)
                    <li class="active">
                        @if (array_has($item, 'icon'))
                            <i class="fa fa-{{ $item['icon'] }}"></i>
                        @endif
                        {{ $item['text'] }}
                    </li>
                @else
                <li>
                    <a href="{{ array_get($item, 'url') }}">
                        @if (array_has($item, 'icon'))
                            <i class="fa fa-{{ $item['icon'] }}"></i>
                        @endif
                        {{ $item['text'] }}
                    </a>
                </li>
                @endif
            @endforeach
        </ol>
        @endif
        <!-- breadcrumb end -->

    </section>
    @endif

    <section class="content">
        @include('admin::partials.alerts')
        @include('admin::partials.exception')
        @include('admin::partials.notice')
        {!! $content !!}
    </section>
    <div class="fixed-bottom-btn"><a id="return-top"><i class="glyphicon glyphicon-arrow-up"></i></a></div>
@endsection