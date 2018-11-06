@include('admin::debug.button')
@include('admin::debug.script')

<template type="text/html" id="debugger-content">
    <div class="debugger-header clearfix">
        <a class="debugger-title " data-action="route-box"><i class="fa fa-internet-explorer"></i> Route </a>
        <a class="debugger-title" data-action="console-box"><i class="fa fa-terminal"></i> Console <span class="badge bg-blue">0</span></a>
        <a class="debugger-title " data-action="sql-box"><i class="fa fa-database"></i> Sql <span class="badge bg-blue">0</span></a>
        {{--<a class="debugger-title">Views <span class="badge bg-blue"></span></a>--}}
        <a class="debugger-title" data-action="session-box">Session  <span class="badge bg-blue">0</span></a>

        <a class="debugger-no-title pull-right" >
            <span class="debugger-clear-history btn btn-xs btn-danger"><i class="fa fa-close"></i> Clear</span>
        </a>
        <a class="debugger-no-title pull-right" style="margin-left:15px;padding:0;padding-top:1.5px">
            <select class="select-path ">
                %options%
            </select>
        </a>
    </div>
    <div class="debugger-body">%body%</div>
</template>