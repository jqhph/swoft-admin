layer.config({maxmin:true,moveOut:true});
LA.success = function (msg, offset) {
    layer.msg(msg, {icon:1, offset: offset||'t', anim:4});
};
LA.error = function (msg, offset) {
    layer.msg(msg, {icon:2, offset: offset||'t', anim:4});
};
LA.warning = function (msg, offset) {
    layer.msg(msg, {icon:3, offset: offset||'t', anim:4});
};
LA.info = function (msg, offset) {
    layer.msg(msg, { offset: offset||'t', anim:6});
};
LA.confirm = function (msg, callback, confirmBtn, cancelBtn) {
    layer.msg(msg, {
        time: 0,
        icon: 3,
        btn: [confirmBtn || 'Confirm', cancelBtn || 'Cancel'],
        yes: function (i) {
            layer.close(i);
            callback(i);
        }
    });
};

/**
 * pjax刷新页面
 *
 * @param url
 */
LA.reload = function (url) {
    var data = {container:'#pjax-container'};
    if (url) {
        data.url = url;
    }
    $.pjax.reload(data);
};

$.pjax.defaults.timeout = 5000;
$.pjax.defaults.maxCacheLength = 0;
// $(document).pjax('a:not(a[target="_blank"])', {
//     container: '#pjax-container'
// });
$(document).pjax('a:not(a[target="_blank"])', '#pjax-container', { fragment: 'body' });
NProgress.configure({parent: '#pjax-container'});

$(document).on('pjax:timeout', function (event) {
    event.preventDefault();
});

$(document).on('submit', 'form[pjax-container]', function (event) {
    $.pjax.submit(event, '#pjax-container')
});

$(document).on("pjax:popstate", function () {

    $(document).one("pjax:end", function (event) {
        $(event.target).find("script[data-exec-on-popstate]").each(function () {
            $.globalEval(this.text || this.textContent || this.innerHTML || '');
        });
    });
});

$(document).on('pjax:send', function (xhr) {
    if (xhr.relatedTarget && xhr.relatedTarget.tagName && xhr.relatedTarget.tagName.toLowerCase() === 'form') {
        $submit_btn = $('form[pjax-container] :submit');
        if ($submit_btn) {
            $submit_btn.button('loading')
        }
    }
    NProgress.start();
});

$(document).on('pjax:complete', function (xhr) {
    if (xhr.relatedTarget && xhr.relatedTarget.tagName && xhr.relatedTarget.tagName.toLowerCase() === 'form') {
        $submit_btn = $('form[pjax-container] :submit');
        if ($submit_btn) {
            $submit_btn.button('reset')
        }
    }
    NProgress.done();
    init_return_top();
});

$(function () {
    $('.sidebar-menu li:not(.treeview) > a').on('click', function () {
        var $parent = $(this).parent().addClass('active');
        $parent.siblings('.treeview.active').find('> a').trigger('click');
        $parent.siblings().removeClass('active').find('li').removeClass('active');
    });

    $('[data-toggle="popover"]').popover();

    init_return_top();
});

(function ($) {
    $.fn.admin = LA;
    $.admin = LA;
})(jQuery);

function init_return_top()
{
    var $top = $('#return-top');
    // 滚动锚点
    $(window).scroll(function () {
        var scrollTop = $(this).scrollTop(), // 滚动条距离顶部的高度
            windowHeight = $(this).height();  // 当前可视的页面高度
        // 显示或隐藏滚动锚点
        if(scrollTop + windowHeight >= 1100) {
            $top.show(100)
        } else {
            $top.hide(100)
        }
    });
    // 滚动至顶部
    $top.click(function () {
        $("html, body").animate({
            scrollTop: $(".swoft-admin-body").offset().top
        }, {duration: 500, easing: "swing"});
        return false;
    })
}