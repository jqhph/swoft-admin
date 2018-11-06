<style>
    .debugger-btn-wrapper {
        height: 35px;
        position: fixed;
        right: 18px;
        bottom:5px;
        z-index: 999999999;
    }

    #btn-debug {
        position: relative;
    }

    .debugger-header {
        min-height: 34px;
        line-height: 20px;
        padding-left: 20px;
        background: #f5f5f5;
    }

    .debugger-no-title,.debugger-title {
        padding: 6px 8px;
        color: #333;
        cursor: pointer;
        float: left;
        min-height:38px;
        line-height:27px;
    }
    .debugger-no-title option{
        padding:5px 8px;
    }
    .debugger-no-title:hover,a.debugger-title:hover {
        background: #ddd;
        color: #333
    }

    .debugger-body {
        padding: 0 20px;
    }

    .debugger-body table tr.active td {
        background: rgb(255, 255, 213) !important;
    }

    .debugger-body .debugger-tab-content {
        display: none
    }

    .debugger-ext {
        color: #888;
        margin-left: 10px;
    }

    .debugger-body .route-time {
        color: #1976d2 !important;
        width: 150px;
    }

    .debugger-header a.active {
        background: #1976d2;
        color: #fff;

    }
    .debugger-body tr{
        cursor: pointer;
    }

    .debugger-header a.active .badge {
        background: #fff !important;
        color: #1976d2 !important;
    }
    .debugger-body .th{
        color:#37474F;
        font-family:'Nunito','Roboto', -apple-system, WenQuanYi Micro Hei, sans-serif!important;
    }
    code.print-text{
        color: #333;
        /*white-space: pre-wrap;*/
        display: block;
        background-color:rgba(255,248,225,0.4)!important;
    }
    code.print-text .keyword{
        font-weight: bold;
    }
    code.print-text .number{
        color:#099
    }
    code.print-text .string{
        color:#c7254e
    }
    .select2-container--open{
        z-index:9999999999999999999;
    }
    .select2-container--default .select2-results>.select2-results__options{
        max-height:260px;
    }
    .debugger-header select.select-path{
        width:420px;background:#fff;padding:5px;border-color:#ddd;
    }
</style>
<div class="debugger-btn-wrapper">
    <button id="btn-debug" class="btn btn-success"><i class="fa fa-bug"></i> &nbsp;Console</button>
</div>

<script>
$(function () {
    var $debug = $('#btn-debug');
    $debug.click(function () {
        $debug.hide();
        var html = DEBUGGER.render();

        layer.open({
            type: 1,
            content: html,
            title: '<i class="fa fa-bug"></i> &nbsp;Console',
            shadeClose: true,
            shade: false,
            area: ['75%', '70%'],
            offset: 'rb',
            end: function () {
                $debug.show();
            }
        });
        DEBUGGER.showCurrentRequestBox();
        DEBUGGER.bind();
    });
});
</script>