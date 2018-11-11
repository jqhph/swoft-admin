@if(!empty($frames))
    <?php $error = $errors->getBag('exception');?>
    <div class="box-header">

        <i class="fa fa-file-code-o" style="color: #4c748c;"></i>
        <h3 class="box-title">Exception Trace</h3>

    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="browser-window">

            @if (!empty($error->get('code')[0]) || !empty($error->get('message')[0]))
                <table class="table args" style="margin: 0px;">
                    <tbody>
                    <tr>

                        <td style="width: 40px;">&nbsp;</td>
                        <td class="name"><strong>Exception</strong></td>
                        <td class="value"><code>{{ $error->get('type')[0] }}</code></td>
                    </tr>
                    @if (!empty($error->get('code')[0]))
                        <tr>
                            <td style="width: 40px;">&nbsp;</td>
                            <td class="name"><strong>Code</strong></td>
                            <td class="value">{{ $error->get('code')[0] }}</td>
                        </tr>
                    @endif
                    <tr>
                        @if (!empty($error->get('message')[0]))
                            <td style="width: 40px;">&nbsp;</td>
                            <td class="name"><strong>Message</strong></td>
                            <td class="value"><strong class="red"><em>{{ $error->get('message')[0] }}</em></strong></td>
                        @endif
                    </tr>
                    </tbody>
                </table>
            @endif

            @foreach($frames as $index => $frame)
                <div data-toggle="collapse" data-target="#frame-{{ $index }}" style="border-top: 2px #fff solid;padding: 10px 0px 10px 20px; background-color: #f3f3f3">
                    <i class="fa fa-info" style="color: #4c748c;"></i>&nbsp;&nbsp;&nbsp;&nbsp;
                    <a href="javascript:void(0);">{{ str_replace(Swoft\App::getAlias('@root'), '', $frame->file()) }}</a>
                    in <a href="javascript:void(0);">{{ $frame->method() }}</a> at line <span class="badge badge-info">{{ $frame->line() }}</span>
                </div>
                <div class="window-content collapse {{ $index == 0 ? 'in' : '' }}" id="frame-{{ $index }}">
                    <pre style="border-radius:0" data-start="{!! $frame->getCodeBlock()->getStartLine() !!}" data-line="{!! $frame->line()-$frame->getCodeBlock()->getStartLine()+1  !!} " class="language-php line-numbers"><code>{!! $frame->getCodeBlock()->output() !!}</code></pre>
                    <table class="table args" style="background-color: #FFFFFF; margin: 10px 0px 0px 0px;">
                        <tbody>
                        @foreach($frame->args() as $name => $val)
                            <tr>
                                <td style="width: 40px;">&nbsp;</td>
                                <td class="name"><strong>{{ $name }}</strong></td>
                                <td class="value">{{ $val }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

            @endforeach
        </div>

    </div>
@endif