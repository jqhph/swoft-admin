@if(isset($title))
    <div class="card-header with-border">
        <h3 class="box-title"> {{ $title }}</h3>
    </div>
@endif

@php
    $__id = uniqid();

    $createButton = $grid->renderCreateButton();
    $exportButton = $grid->renderExportButton();
    $tools        = $grid->renderHeaderTools();
@endphp
@if ($createButton || $exportButton || $tools)
    <div class="card-header">
        <div class="pull-right" data-responsive-table-toolbar="{{$__id}}">
            {!! $createButton !!}
            {!! $exportButton !!}
        </div>
        <span>
    {!! $tools !!}
    </span>
        <div style="clear:both;height:0"></div>
    </div>
@endif
{!! $grid->renderFilter() !!}

<div class="card-body card-padding panel-collapse collapse in table-responsive">
    <table class="table table-hover responsive {{ $grid->option('useBordered') ? 'table-bordered' : '' }} " id="{{$__id}}">
        <thead>
        @if ($headers = $grid->getHeaders())
            <tr>
                @foreach($headers as $header)
                    {!! $header->render() !!}
                @endforeach
            </tr>
        @endif
        <tr>
            @foreach($grid->getColumns() as $column)
                <th {!! $column->formatTitleAttributes() !!} width="{{ $column->width() }}">{!! $column->getLabel() !!}{!! $column->sorter() !!}</th>
            @endforeach
        </tr>
        </thead>

        <tbody>
        @foreach($grid->rows() as $row)
            <tr {!! $row->getRowAttributes() !!}>
                @foreach($grid->columnNames as $name)
                    <td {!! $row->getColumnAttributes($name) !!}>
                        {!! $row->column($name) !!}
                    </td>
                @endforeach
            </tr>
            @if ($tree = $grid->pullTree())
                {!! $tree->render() !!}
            @endif
            @if ($grid->hasExpands())
                {!! $grid->renderExpands() !!}
            @endif
        @endforeach
        @if (empty(count($grid->rows())))
            <tr>
                <td>
                    <div style="margin:5px 0 0 10px;"><span class="help-block" style="margin-bottom:0"><i class="fa fa-info-circle"></i>&nbsp;{{ t('No data.', 'admin') }}</span></div>
                </td>
            </tr>
        @endif
        </tbody>

        {!! $grid->renderFooter() !!}

    </table>
</div>
@if ($paginator = $grid->paginator())
    <div class="box-footer clearfix" style="background:transparent">
        {!! $paginator !!}
    </div>
@endif
