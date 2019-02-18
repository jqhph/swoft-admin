<div class="btn-group" style="margin-right:3px">
    <label class="btn btn-dropbox {{ $btn_class }} btn-sm">
        <i class="fa fa-filter"></i><span class="hidden-xs">&nbsp;&nbsp;{{ t('Filter', 'admin') }}</span>
    </label>
    @if($scopes->count() == 1)
        @foreach($scopes as $key => $collections)
            <a class="btn {{ $filter->getScopeCurrentStyle($key) }} btn-sm dropdown-toggle" data-toggle="dropdown">
                <span>&nbsp;{{ $filter->getScopeCurrentLabel($key) }}&nbsp;</span>
                <span class="caret"></span>
                <span class="sr-only"></span>
            </a>
            <ul class="dropdown-menu" role="{{ $key }}" >
                @foreach($collections as $scope)
                    {!! $scope->render() !!}
                @endforeach
                <li role="separator" class="divider"></li>
                <li><a href="{{ $scope->getCancelUrl() }}">{{ $collections->getCancelLabel() }}</a></li>
            </ul>
        @endforeach
    @endif
</div>

@if(($scopesCount = $scopes->count()) > 1)
    <div class="btn-group" style="margin-right:5px">
        <ul class="filter-scope">
            @php
                $i = 0;
            @endphp
            @foreach($scopes as $key => $collections)
                @php
                    $i++;
                @endphp
                <li class="pull-left">
                    <a class="btn {{ $filter->getScopeCurrentStyle($key) }} btn-sm dropdown-toggle" data-toggle="dropdown">
                        <span>&nbsp;{{ $filter->getScopeCurrentLabel($key) }}&nbsp;</span>
                        @if ($scopesCount == $i)
                            <span class="caret"></span>
                        @endif
                        <span class="sr-only"></span>
                    </a>
                    <ul class="dropdown-menu" >
                        @foreach($collections as $scope)
                            {!! $scope->render() !!}
                        @endforeach
                        <li role="separator" class="divider"></li>
                        <li><a href="{{ $scope->getCancelUrl() }}">{{ $collections->getCancelLabel() }}</a></li>
                    </ul>
                </li>
            @endforeach
        </ul>
    </div>
    <style>.filter-scope{list-style:none;padding:0;margin:0;display:inline}.filter-scope li.dro{position:relative;}</style>
@endif
