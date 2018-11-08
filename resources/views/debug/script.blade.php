{!! html_js('@admin/ajax-hook/ajax-hook.min.js') !!}
<script>
    (function (w) {
        var allData = {},
            current,
            currentKey,
            allRoutes = [],
            history, // 历史记录
            historyRoutes = [], // 历史路由信息
            maxHistoryNum = 300;

        function Debugger() {
            var self = this;
            hookAjax({
                // 拦截ajax请求
                onreadystatechange: render_ajax_traces,
                onload: render_ajax_traces
            });

            setup_history();

            function render_ajax_traces(xhr) {
                var traces = get_traces(xhr.responseText);
                if (!traces) {
                    return;
                }
                var resp = JSON.parse(xhr.responseText);
                delete resp['__traces__'];
                // 增加response反馈信息
                traces.route.response = resp;
                self.add(traces);
                self.rerender();
            }
            function get_traces(text) {
                try {
                    var obj = JSON.parse(text);
                    return obj.__traces__ || false;
                } catch (e) {
                    return false;
                }
            }
        }

        function setup_history() {
            history = w.localStorage.getItem('__debug_history__');
            if (!history) {
                history = [];
                return;
            }

            history = JSON.parse(history);

            var i, id, item;
            for (i in history) {
                item = history[i];
                id = uuid();
                allData[id] = item;
                item.route.id = id;

                historyRoutes.push(item.route);
            }
        }

        // 保存历史记录
        function save() {
            history.unshift(current);
            history = history.splice(0, maxHistoryNum - 1);
            w.localStorage.setItem('__debug_history__', JSON.stringify(history))
        }

        function clear_history() {
            history = [];
            historyRoutes = [];
            w.localStorage.setItem('__debug_history__', '[]')
        }

        function build_object_row(obj, col) {
            var span = '';
            if (col) {
                span = "colspan=\""+col+"\"";
            }
            return " <tr><td "+span+" style='padding:0;border:0;'><div class=\"panel-collapse collapse\"><code class=\"print-text\">\n"
                + build_object_text(obj)
                + "\n</code></div></td></tr>"
        }

        /**
         *  把json对象转化为html
         * @param obj
         * @param level
         * @returns {*}
         */
        function build_object_text(obj, level) {
            if (!obj) return '';
            if (typeof obj === 'string') {
                obj = JSON.parse(obj);
            }
            if (typeof obj !== 'object') {
                return '';
            }
            level = level || 1;
            var len = objlength(obj), br ='<br/>', item;
            var html = '<span class="keyword">array</span>:<span class="number">'+len+"</span> ["+br;
            for (var i in obj) {
                if (is_number(i)) {
                    html += space(4*level) + '<span class="number">'+ i +"</span> =&gt; ";
                } else {
                    html += space(4*level) + '<span class="string">"'+ i +"\"</span> =&gt; ";
                }

                item = obj[i];
                if (typeof obj[i] === 'object') {
                    html += build_object_text(item, level + 1) + br;
                } else if (is_float(obj[i]) || is_number(obj[i])) {
                    html += '<span class="number">'+ item +"</span>,"+br;
                } else {
                    html += '<span class="string">"'+ item +"\"</span>,"+br;
                }
            }

            return html + space(4*(level-1)) + "]";

            function space(n) {
                return new Array(n+1).join('&nbsp;');
            }

            function is_float(str) {
                str = String(str);
                for (i = 0; i < str.length; i++) {
                    if ((str.charAt(i) < "0" || str.charAt(i) > "9") && str.charAt(i) != '.') {
                        return false;
                    }
                }
                return true;
            }
            function is_number(str) {
                str = String(str);
                if (str.match(/^\d*$/) == null)
                    return false;
                else
                    return true;
            }
        }

        /**
         * 构建控制台html
         *
         * @param opt
         */
        function build_console_box(opt) {
            if (typeof opt !== 'object' || !objlength(opt)) {
                return '';
            }

            var html = ' <div class="debugger-tab-content console-box"><table class="table table-hover">';

            for (var i in opt) {
                if (typeof opt[i] !== 'object' || !objlength(opt[i])) {
                    continue;
                }
                html += build_row(opt[i]);
            }
            return html + "</table></div>";


            function build_row(row) {
                var date = row.date,
                    content = row.content,
                    context = row.context||[],
                    file = row.file || '',
                    hasContext = objlength(context);

                var html = "<tr onclick=\"$(this).next().find('.collapse').collapse('toggle');\"><td>"
                    + "<span class=\"label label-info\" >"+ date +"</span> &nbsp;"
                    + "<span class=\"th\">"+ content +"</span>&nbsp;"
                    + (hasContext ? " <code>" + jsonstring(context) + "</code><span style=\"color:#333\" class=\"caret\"></span>" : "")
                    + (file ? "<span class=\"debugger-ext\">&nbsp; <i class=\"fa fa-file-o\"></i> " + file + "</span>" : '')
                    + "<td></tr>";

                if (!hasContext) {
                    return html;
                }
                return html + build_object_row(context)
            }
        }

        function build_sql_box(opt) {
            if (typeof opt !== 'object' || !objlength(opt)) {
                return '';
            }
            var html = " <div class=\"debugger-tab-content sql-box\" ><table class=\"table table-hover\">";
            for (var i in opt) {
                if (typeof opt[i] !== 'object' || !objlength(opt[i])) {
                    continue;
                }
                html += build_row(opt[i]);
            }

            return html + "</table></div>";

            function build_row(row) {
                var sql = row.sql,
                    file = row.file || '',
                    trace = row.traces,
                    cost = row.cost || '';

                if (cost) cost += 'ms';

                return "<tr class=\"sql\" onclick=\"$(this).next().find('.collapse').collapse('toggle');\">"
                    + "<td width=\"50%\"><code>"+ sql +"</code></td>"
                    + "<td><span class=\"debugger-ext\">"
                    + (cost ? ("<i class=\"fa fa-clock-o\"></i> "+cost+"&nbsp; &nbsp;") : '')
                    + (file ? ("<i class=\"fa fa-file-o\"></i> "+file+"&nbsp; &nbsp;") : '')
                    + "</span><span style=\"color:#333\" class=\"caret\"></span></td></tr>"
                    + "<tr><td colspan='2' style='padding:0;border:0;'><div class=\"panel-collapse collapse\">"
                    + "<div style=\"padding:10px\"><pre class=\"dump\">"
                    + trace + "</pre></div></div></td></tr>";
            }
        }

        function build_route_box(opt) {
            if (typeof opt !== 'object' || !objlength(opt)) {
                return '';
            }
            var path = opt.path,
                method = opt.method,
                controller = opt.controller,
                query = opt.query || [],
                post = opt.post || [],
                reqtime = opt.datetime,
                type = opt.type,
                response = opt.response || '',
                cost = opt.cost,
                logid = opt.logid,
                status = opt.status || '';

            if (status > 300 && status < 400) {
                status = "<span class='label label-warning'>302</span> &nbsp; ";
            } else if (status && status >= 400) {
                status = "<span class='label label-danger'>"+status+"</span> &nbsp; ";
            }

            var html = "<div class=\"debugger-tab-content route-box\" ><table class=\"table table-hover\">" +
                "<tr ><td class='th'>Uri</td><td>" +
                "<span class='label label-info'>"+reqtime+"</span> " +
                "<span class='label label-info'>"+method+"</span> &nbsp; <code>"+path+"</code>  " +
                "</td></tr>" +
                "<tr><td class='th'>Type</td><td>"+status+"<span class='label label-success'>"+ type +
                "</span> &nbsp; <span class='label label-success'><i class='fa fa-clock-o '></i> "+cost+"</span></td></tr>" +
                "<tr><td>Logid</td><td><code>"+ logid +"</code></td></tr>" +
                "<tr><td class='th'>Controller</td><td><code>"+controller+"</code></td></tr>" +
                "<tr onclick=\"$(this).next().find('.collapse').collapse('toggle');\"><td class='th'>Query</td>" +
                "<td><code>"+jsonstring(query)+"</code><span style=\"color:#333\" class=\"caret\"></span></td></tr>";

            if (objlength(query)) {
                html += build_object_row(query, 2);
            }
            html += " <tr onclick=\"$(this).next().find('.collapse').collapse('toggle');\"><td class='th'>Post</td>" +
                "<td><code>"+jsonstring(post)+"</code><span style=\"color:#333\" class=\"caret\"></span></td></tr>";
            if (objlength(post)) {
                html += build_object_row(post, 2);
            }
            if (response) {
                html += " <tr onclick=\"$(this).next().find('.collapse').collapse('toggle');\"><td class='th'>Response</td>" +
                    "<td><code>"+jsonstring(response)+"</code><span style=\"color:#333\" class=\"caret\"></span></td></tr>";
                if (objlength(response)) {
                    html += build_object_row(response, 2);
                }
            }

            return html + "</table></div>";
        }

        function build_session_box(opt) {
            if (typeof opt !== 'object' || !objlength(opt)) {
                return '';
            }
            var html = " <div class=\"debugger-tab-content session-box\" ><table class=\"table table-hover\">";

            for (var i in opt) {
                var item = opt[i];
                if (typeof opt[i] === 'object') {
                    item = jsonstring(item) + ' <span style="color:#333" class="caret"></span>'
                }
                html += '<tr onclick="$(this).next().find(\'.collapse\').collapse(\'toggle\');">' +
                    '<td class="th"><span class="label label-info">'+i+'</span></td><td><code>'+item+'</code></td></tr>';
                if (typeof opt[i] === 'object') {
                    html += build_object_row(opt[i], 2)
                }
            }

            return html + '</table></div>';
        }

        function build_options() {
            var historyOptions = "";

            var options = '', i, item, selected = '', id;
            for (i in allRoutes) {
                item = (allRoutes[i].reqtime || null) + ' ' + allRoutes[i].method + ' ' + allRoutes[i].path;
                id = allRoutes[i].id;
                selected = '';
                if (id == currentKey) {
                    selected = "selected='selected'";
                }
                options += "<option " + selected + " value='" + id + "'>"+item+"</option>";
            }

            if (historyRoutes.length) {
                historyOptions = '<optgroup label="Local History: '+historyRoutes.length+'">';
                for (i in historyRoutes) {
                    item = (historyRoutes[i].datetime || null) + ' ' + historyRoutes[i].method + ' ' + historyRoutes[i].path;
                    id = historyRoutes[i].id;
                    selected = '';
                    if (id == currentKey) {
                        selected = "selected='selected'";
                    }
                    historyOptions += ("<option " + selected + " value='" + id + "'>"+item+"</option>");
                }
                historyOptions += '</optgroup>';
            }


            return options + historyOptions;
        }

        /**
         * 获取json对象长度
         *
         * @param obj
         * @returns {number}
         */
        function objlength(obj) {
            if (typeof obj !== 'object') {
                return 0;
            }
            var i, l = 0;
            for(i in obj) {
                l += 1;
            }
            return l;
        }

        function uuid() {
            var d = new Date().getTime();
            var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxx'.replace(/[xy]/g, function(c) {
                var r = (d + Math.random()*16)%16 | 0;
                d = Math.floor(d/16);
                return (c=='x' ? r : (r&0x3|0x8)).toString(16);
            });
            return uuid;
        };

        function jsonstring(obj) {
            if (typeof obj !== 'object') {
                return '';
            }
            var def = JSON.stringify(obj);
            if (def.length > 200) {
                def = def.substr(0, 200) + ' ...' + def.substr(-1, 1)
            }

            return def;
        }

        /**
         * 获取当前请求debug内容盒子id
         */
        function $id(k) {
            return $('#cc'+(k || currentKey));
        }

        Debugger.prototype = {
            // 增加请求debug日志
            add: function (opt) {
                var key = uuid();
                allData[key] = opt;
                this.select(key, true);
                // 保存历史记录
                save();
            },

            /**
             * 选中请求
             */
            select: function (k, addRoute) {
                current = allData[k];
                currentKey = k;
                if (addRoute) {
                    current.route.id = k;
                    allRoutes.unshift(current.route);
                }
            },

            rerender: function () {
                var $wrapper = $('.debugger-wrapper');
                if ($wrapper.length < 1) {
                    return;
                }

                var tpl = $('#debugger-content').html(),
                    builds = this.build(),
                    options = builds[0],
                    body = builds[1];
                $wrapper.html(tpl.replace('%body%', body).replace('%options%', options));

                this.showCurrentRequestBox();
                this.bind();
            },

            render: function () {
                var tpl = $('#debugger-content').html(),
                    builds = this.build(),
                    options = builds[0],
                    body = builds[1];

                return "<div class=\"debugger-wrapper\">" + tpl.replace('%body%', body).replace('%options%', options) + "</div>";
            },

            /**
             * 绑定事件
             */
            bind: function () {
                var $title = $('.debugger-title'), self = this;
                // tab标题点击事件
                $title.click(function () {
                    $title.removeClass('active');
                    var $t = $(this);
                    $t.addClass('active');

                    // 隐藏所有盒子
                    $('.debugger-tab-content').hide();
                    // 选中当前请求下的对应的盒子
                    $id().find('.'+$t.data('action')).show();
                });
                // 默认选中第一个标题
                $title[0] && $title[0].click();

                // 请求路由选择
                var $options = $('.debugger-wrapper .select-path');
                $options.select2();
                $options.on('change', function () {
                    var val = $(this).val();
                    self.select(val);
                    self.rerender();
                });

                $('.debugger-clear-history').click(function () {
                    LA.confirm('Are you sure to clear history?', function () {
                        LA.info('Clear succeeded!');

                        clear_history();
                        self.select(allRoutes[0].id);
                        self.rerender();
                    })
                });
            },

            /**
             * 显示当前请求盒子
             */
            showCurrentRequestBox: function () {
                $('.debugger-row').hide();
                $id().show();

                // console日志数量
                var logsNum = objlength(current.logs),
                    sqlNum = objlength(current.queries),
                    sessionsNum = objlength(current.session);

                $('[data-action="console-box"] .badge').text(logsNum);
                $('[data-action="sql-box"] .badge').text(sqlNum);
                $('[data-action="session-box"] .badge').text(sessionsNum);
            },

            build: function () {
                if (!current || !currentKey) {
                    LA.error("无法获取初始信息,构建debugger控制台失败");
                    return '';
                }

                var body = "<div id='cc"+currentKey+"' class='row debugger-row' style='display:none'>"
                    + build_route_box(current.route||{})
                    + build_console_box(current.logs||[])
                    + build_sql_box(current.queries||[])
                    + build_session_box(current.session)
                    +'</div>';

                return [build_options(), body];
            }
        };

        w.DEBUGGER = new Debugger();

        DEBUGGER.add({!! Swoft\Admin\Debugger\Collector::output() !!});
    })(window);
</script>