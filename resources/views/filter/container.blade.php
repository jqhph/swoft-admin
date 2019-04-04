<div class="panel-collapse collapse {{ $expand?'in':'' }}">
    <div style="{!! $style !!}" class="box-header with-border" id="{{ $filterID }}"> {{--with-border--}}
        <form action="{!! $action !!}" class="form-horizontal" pjax-container method="get">
                @foreach($layout->columns() as $column)
                    @foreach($column->filters() as $filter)
                        {!! $filter->render() !!}
                    @endforeach
                @endforeach
            <div class="pull-left">
                <div class="btn-group btn-group-sm" style="margin-left:5px;">
                    <button class="btn btn-info btn-sm">
                        <i class="fa fa-search"></i>&nbsp;&nbsp;{{ t('Search', 'admin') }}
                    </button>
                </div>
                <div class="btn-group btn-group-sm default" style="margin-left:8px"  >
                    @if($disableResetButton)
                    <button type="reset" class="btn btn-default btn-sm ">
                        {{ t('Reset', 'admin') }}&nbsp;&nbsp;<i class="fa fa-undo"></i>
                    </button>
                    @endif
                    @if($disableRefreshButton)
                    <a class="btn btn-default btn-sm " href="{{$resetUrl}}">
                        {{ t('Refresh', 'admin') }}&nbsp;&nbsp;<i class="fa fa-refresh"></i>
                    </a>
                    @endif
                </div>
            </div>
            <div style="clear:both"></div>
        </form>
    </div>
</div>