window.onscroll = function () {
    if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
        document.getElementById("rocket").style.display = "block";
    } else {
        document.getElementById("rocket").style.display = "none";
    }
}

let sub_pages = new Map();

let rcm = $("#recommend");
let side_bar = $("#side-bar");

let emotions = "";

function get_emotions() {
    $.ajax({
        url: "./api.php",
        type: 'POST',
        data: {
            "type": "get-emotions-array"
        },
        async: true,
        success: function(data) {
            let obj = JSON.parse(data);
            emotions = "";
            let count = 0;
            for (let emotion in obj) {
                if (count === 0) emotions += "<tr>";
                count++;
                emotions += '<td onclick="input_emotion(\'[' + emotion + ']\')" class="emotion-block"><img title="[' + emotion + ']" alt="[' + emotion + ']" src="' + obj[emotion] + '" style="width: 64px;">';
                if (count === 5) {
                    emotions += "</tr>";
                    count = 0;
                }
            }
            update_emotion_tables();
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}
function load_toggles() {
    $('.emotion-toggle').popup({
        on: 'click',
        position : 'right center'
    });
}
function update_emotion_tables() {
    $('#emotions-select').each(function () {
        $(this).html(emotions);
    });
    load_toggles();
}
function input_emotion(text) {
    let div = $("#reply-textarea-sub");
    div.val(div.val() + text);
    $('.emotion-toggle').popup('hide');
}
function show_modal(modal) {
    $("#" + modal).modal("show");
}
function hide_delete_modal(modal) {
    $("#" + modal).modal("hide");
}
function delete_blog(id) {
    hide_delete_modal();
    $.ajax({
        url: "./api.php",
        type: 'POST',
        data: {
            "type": "delete",
            "id": id
        },
        async: true,
        success: function(data) {
            if (data !== "success") {
                alert("删除失败，" + data);
            } else {
                window.location.href = "/";
            }
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
            alert("删除失败，未知错误");
        }
    });
}
function post_reply(floor, sub) {
    let btn = $("#reply-button" + (sub ? "-" + floor : ""));
    btn.addClass("disabled loading");
    let textarea_div = $(sub ? "#reply-textarea-sub" : "#reply-textarea");
    $.ajax({
        url: "./api.php",
        type: 'POST',
        data: {
            "type": "post-reply",
            "sub": sub,
            "in_blog": blog_id,
            "text": textarea_div.val(),
            "floor": floor
        },
        async: true,
        success: function(data) {
            console.log(data);
            try {
                let obj = JSON.parse(data);
                if (obj['statu'] === "success") {
                    $("#replier-sub-div").remove();
                    if (!sub) sub_pages.set(floor + 1, 0);
                    let div = $(sub ? "#reply-subs-" + floor : "#blog-replies");
                    div.css("display", "block");
                    div.prepend(obj_to_str(obj['reply'], sub));
                    textarea_div.val("");
                } else {
                    alert("评论失败");
                }
            } catch (e) {
                alert("评论失败，" + data);
            }
            btn.removeClass("loading");
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert("评论失败：" + XMLHttpRequest.responseText);
        }
    });
    setTimeout(function () {
        btn.removeClass("disabled");
    }, 5000);
}
function jump_to_reply() {
    let div = $('#reply-id-' + reply_id);
    let top = div.offset().top;
    $(window).scrollTop(top - 60);
    div.addClass("highlighter");
    setTimeout(function () {
        div.removeClass("highlighter");
    }, 2000);
    reply_id = turn_to_subpage = -1;
}
function get_reply(page) {
    curpage = page;
    $.ajax({
        url: "./api.php",
        type: 'POST',
        data: {
            "type": "get-reply",
            "in_blog": blog_id,
            "page": page
        },
        async: true,
        success: function(data) {
            parse_replies(data);
            side_bar.sticky();
            if (turn_to_subpage >= 0) get_sub_reply(turn_to_subpage, turn_to_floor);
            if (reply_id >= 0 && turn_to_subpage < 0) {
                jump_to_reply();
            }
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
            side_bar.sticky();
        }
    });
}
function get_sub_reply(page, floor) {
    sub_curpage.set(floor, page);
    $.ajax({
        url: "./api.php",
        type: 'POST',
        data: {
            "type": "get-sub-reply",
            "in_blog": blog_id,
            "page": page,
            "floor": floor
        },
        async: true,
        success: function(data) {
            let obj = JSON.parse(data);
            let div = $("#reply-subs-" + floor);
            div.empty();
            div.append(obj_to_subs(obj));
            div.append(pages_selector(page, sub_pages.get(floor), ((page, num) => '<a onclick="get_sub_reply(' + page + ',' + floor + ')" class="item">' + num + '</a>'), 'margin-top: 16px', 'sub-' + floor));
            if (reply_id >= 0 && turn_to_subpage >= 0) {
                jump_to_reply();
            }
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}
function try_delete_reply(floor, sub, sub_floor) {
    $("#delete-reply-modal #submit").attr("onclick", "delete_reply(" + floor + "," + sub + "," + sub_floor + ")");
    show_modal("delete-reply-modal");
}
function delete_reply(floor, sub, sub_floor) {
    hide_delete_modal("delete-reply-modal");
    $.ajax({
        url: "./api.php",
        type: 'POST',
        data: {
            "type": "delete-reply",
            "in_blog": blog_id,
            "floor": floor,
            "sub": sub,
            "sub_floor": sub_floor
        },
        async: true,
        success: function(data) {
            if (data !== "success") {
                alert(data);
            } else {
                $("#reply-" + floor + sub + sub_floor + "-div").hide();
            }
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}
function parse_replies(data) {
    let obj = JSON.parse(data);
    let div = $("#blog-replies");
    div.empty();
    for (let i = 0; i < obj.length; ++i) {
        sub_pages.set(obj[i]['floor'], parseInt((obj[i]['sub_sum'] - 1) / 5));
        if (sub_curpage.get(obj[i]['floor']) === undefined) sub_curpage.set(obj[i]['floor'], 0);
        div.append(
            obj_to_str(obj[i], false)
        );
    }
    $("#page-selector").remove();
    let selector = $("#reply-page-selector");
    selector.empty();
    selector.append(pages_selector(curpage, pages, (page, num) => '<a onclick="get_reply(' + page + ')" class="item">' + num + '</a>'), "");
    update_emotion_tables();
}
function obj_to_str(obj, sub) {
    let user_href = '../user/space.php?uid=' + obj['owner'];
    return '' +
        '<div class="comment" id="reply-' + obj['floor'] + obj['sub'] + obj['sub_floor'] + '-div">' +
        '<a class="avatar" href="' + user_href + '">' +
        '<img src="https://cravatar.cn/avatar/' + obj['owner_emmd5'] + '?d=' + avatar + '" alt="avatar">' +
        '</a>' +
        '<div class="content" id="reply-id-' + obj['reply_id'] + '">' +
        '<a class="author" href="' + user_href + '">' + obj['owner_nickname'] + ' ' + (obj['owner_admin'] ? admin_label : '') + (obj['owner'] === uid ? owner_label : '') + obj['owner_title'] + '</a>' +
        '<div class="metadata">' +
        '<span class="date">' + obj['time'] + '</span>' +
        '</div>' +
        '<div class="text">' +
        obj['text'] +
        '</div>' +
        '<div class="actions" ' + (sub ? '' : 'id="reply-' + obj['floor'] + '"') + '>' +
        (!sub ? '<a class="reply" onclick="show_reply_form(' + obj['floor'] + ', ' + obj['reply_id'] + ')">回复</a>' : '<a class="reply" onclick="show_reply_form(' + obj['floor'] + ', ' + obj['reply_id'] + ', \'' + obj['owner_nickname'] + '\')">回复</a>') +
        (admin || local_uid === obj['owner'] ? '<a class="reply" onclick="try_delete_reply(' + obj['floor'] + ',' + obj['sub'] + ',' + obj['sub_floor'] + ')">删除</a>' : '') +
        '</div>' +
        '</div>' +
        '<div class="comments" style="display: ' + (obj['sub_sum'] > 0 ? "block" : "none") + ';" ' + (sub ? '' : ('id="reply-subs-' + obj['floor'])) + '">' +
        (!sub && obj['sub_sum'] > 0 ? obj_to_subs(obj['subs']) : "") +
        (!sub && sub_pages.get(obj['floor']) > 0 ? pages_selector(0, sub_pages.get(obj['floor']), ((page, num) => '<a onclick="get_sub_reply(' + page + ',' + obj['floor'] + ')" class="item">' + num + '</a>'), 'margin-top: 16px', 'sub-' + obj['floor']) : '') +
        '</div>' +
        '</div>'
}
function obj_to_subs(obj) {
    let str = '';
    for (let i = 0; i < obj.length; ++i) {
        str += obj_to_str(obj[i], true);
    }
    return str;
}
function show_reply_form(floor, id, at = undefined) {
    $("#replier-sub-div").remove();
    $("#reply-id-" + id).append(
        '<form class="ui reply form" id="replier-sub-div">' +
        '<div class="field">' +
        '<textarea id="reply-textarea-sub">' +
        (at !== undefined ? '回复 @' + at + ' :' : '') +
        '</textarea>' +
        '</div>' +
        '<div class="ui primary submit labeled icon button" id="reply-button-' + floor + '" onclick="post_reply(' + floor + ', true)">' +
        '<i class="icon edit"></i>回复' +
        '</div>' +
        '<div class="ui teal button emotion-toggle" style="float: right">表情</div>' +
        '<div class="ui fluid popup" style="max-height: 300px; max-width: 350px; overflow-y: scroll">' +
        '<table id="emotions-select" class="ui very basic collapsing celled table"></table>' +
        '</div>' +
        '</form>'
    );
    update_emotion_tables();
}
let btn = $("#clt-btn");
function add_collect() {
    $.ajax({
        url: "../user/api.php",
        type: 'POST',
        data: {
            "type": "add-collect",
            "blogid": blog_id
        },
        async: true,
        success: function(data) {
            console.log(data);
            if (data !== "success") {
                alert(data);
            } else {
                btn.removeClass();
                btn.addClass("ui mini button");
                btn.empty();
                btn.append('<i class="star icon"></i>已收藏');
                btn.attr("onclick", "dis_collect()");
            }
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}
function dis_collect() {
    $.ajax({
        url: "../user/api.php",
        type: 'POST',
        data: {
            "type": "dis-collect",
            "blogid": blog_id
        },
        async: true,
        success: function(data) {
            console.log(data);
            if (data !== "success") {
                alert(data);
            } else {
                btn.removeClass();
                btn.addClass("ui mini yellow button");
                btn.empty();
                btn.append('<i class="star icon"></i>收藏');
                btn.attr("onclick", "add_collect()");
            }
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}
get_reply(curpage);
function get_copier(tgt) {
    return '<i class="copy outline icon copier copier-float" data-clipboard-target="#' + tgt + '" data-content="复制成功"></i>';
}
let codes = 0;
window.onload = function () {
    setTimeout(function () {
        $('code').each(function () {
            if (!$(this).hasClass("hljs")) return;
            this.id = 'code-' + ++codes;
            $(this).css("position", "relative");
            let txt = $(this).html();
            $(this).html(txt + get_copier('code-' + codes));
        });
        load_copier();
    }, 100)
}
function recommend() {
    $.ajax({
        url: "api.php",
        type: 'POST',
        data: {
            "type": "randomly-select-blogs",
            "amount": 4
        },
        async: true,
        success: function(data) {
            rcm.empty();
            let json = JSON.parse(data);
            for (let i = 0; i < json.length; ++i) {
                let obj = json[i];
                rcm.append('<a href="?id=' + obj['id'] + '">' + obj['title'] + '</a>' + (i === json.length - 1 ? '' : '<div class="ui divider"></div>'));
            }
            rcm.removeClass("loading");
        }
    });
}
get_emotions();
recommend();