function cur_url_decode(url) {
    return url.replace("$", "?").replaceAll("@", "&").replaceAll(",", "=");
}
function cur_url_encode(url) {
    return url.replace("?", "$").replaceAll("&", "@").replaceAll("=", ",");
}
function redirect_to_from(prefix) {
    let url = window.location.search;
    if (url.length > 0) {
        window.location.replace(get_from_url(prefix));
    }
}
function get_from_url(prefix) {
    let url = window.location.search;
    if (url === undefined || url == null || url === "") {
        window.location.replace(prefix + window.location.host);
        return;
    }
    let reg = /from=[\w\-,@$:\/.]+/g;
    let to = url.match(reg);
    to = to == null ? prefix + window.location.host : cur_url_decode(to[0].split("=")[1]);
    return to;
}
function pages_selector(cur, tot, lambda, extra_style, id_fix) {
    if (tot === 0) return '';
    let str = '<div class="ui pagination menu" style="max-width: 100%; overflow: auto; ' + extra_style + '" id="' + id_fix + 'page-selector">';
    str += lambda(Math.max(0, cur - 1), '<i class="angle left icon"></i>');
    if (cur !== 0) {
        str += lambda(0, 1);
    }
    if (cur - 1 > 2) str += lambda(Math.max(0, cur - 5), '...');
    if (cur - 2 > 0) str += lambda(cur - 2, cur - 1);
    if (cur - 1 > 0) str += lambda(cur - 1, cur);
    str += '<a class="item active">' + (cur + 1) + '</a>';
    if (cur + 1 < tot) str += lambda(cur + 1, cur + 2);
    if (cur + 2 < tot) str += lambda(cur + 2, cur + 3);
    if (cur + 1 < tot - 2) str += lambda(Math.min(cur + 5, tot), '...');
    if (cur !== tot) {
        str += lambda(tot, tot + 1);
    }
    str += lambda(Math.min(tot, cur + 1), '<i class="angle right icon"></i>');
    str += '</div>';
    return str;
}
function blog_card_obj_to_str(obj, def) {
    return '' +
        '<span class="card">' +
        '<a class="content" href="/blog/show.php?id=' + obj['id'] + '">' +
        '<div class="header">' + obj['title'] + '</div>' +
        '<div class="meta">' +
        '<span class="right floated time">' + obj['create_time'] + '</span>' +
        '<span class="category">' + obj['tags'] + '</span>' +
        '</div>' +
        '<div class="description">' +
        '<p>' + obj['intro'] + '</p>' +
        '</div>' +
        '<span class="right floated">' +
        '<i class="eye icon"></i>' +
        obj['page_view'] +
        '</span>' +
        '<i class="comment icon"></i>' +
        obj['replies_sum'] +
        '</a>' +
        '<div class="extra content">' +
        '<i class="star icon yellow disabled"></i>' +
        obj['likes'] +
        '<a class="right floated author" href="/user/space.php?uid=' + obj['owner'] + '">' +
        obj['owner_nickname'] + ' <img class="ui avatar image" src="https://cravatar.cn/avatar/' + obj['owner_emmd5'] + '?d=' + def + '" alt="avatar">' +
        '</a>' +
        '</div>' +
        '</span>';
}
function collected_blog_card_obj_to_str(obj) {
    return '' +
        '<span class="card" id="clt-' + obj['id'] + '">' +
        '<a class="content" href="/blog/show.php?id=' + obj['id'] + '">' +
        '<div class="header">' + obj['title'] + '</div>' +
        '<div class="meta">' +
        '<span class="right floated time">' + obj['create_time'] + '</span>' +
        '<span class="category">' + obj['tags'] + '</span>' +
        '</div>' +
        '<div class="description">' +
        '<p>' + obj['intro'] + '</p>' +
        '</div>' +
        '<span class="right floated">' +
        '<i class="eye icon"></i>' +
        obj['page_view'] +
        '</span>' +
        '<i class="comment icon"></i>' +
        obj['replies_sum'] +
        '</a>' +
        '<div class="extra content">' +
        '<div class="ui red tiny button" onclick="dis_collect(' + obj['id'] + ')"><i class="trash alternate icon"></i>删除</div>' +
        '<i class="star icon yellow disabled"></i>' +
        obj['likes'] +
        '<a class="right floated author" href="/user/space.php?uid=' + obj['owner'] + '">' +
        obj['owner_nickname'] + ' <img class="ui avatar image" src="https://cravatar.cn/avatar/' + obj['owner_emmd5'] + '?d=<?php echo Info::$def_user_avatar ?>" alt="avatar">' +
        '</a>' +
        '</div>' +
        '</span>';
}
function get_admin_label()
{
    return '<div class="ui mini teal horizontal label">管理员</div>';
}
function get_title_label_directly(str)
{
    if (str.length === 0) return '';
    return '<div class="ui mini blue horizontal label">' + str + '</div>';
}
function user_card_obj_to_str(obj, def_avatar) {
    return '\
    <div class="card">\
        <a class="image" href="/user/space.php?uid=' + obj['uid'] + '">\
          <img src="https://cravatar.cn/avatar/' + obj['emmd5'] + '?d=' + def_avatar + '" alt="avatar">\
        </a>\
        <a class="content" style="word-break: break-all" href="/user/space.php?uid=' + obj['uid'] + '">\
          <div class="header">' + obj['nickname'] + '</div>\
          <div class="meta">\
            ' + (obj['admin'] ? get_admin_label() : '') + get_title_label_directly(obj['title']) + '\
          </div>\
          <div class="description">\
            ' + obj['intro'] + '\
          </div>\
        </a>\
        <div class="extra content">\
            UID ' + obj['uid'] + '\
        </div> \
    </div>\
    ';
}
function load_copier() {
    $('.copier').popup({
        on: 'click'
    });

    let clipboard = new ClipboardJS('.copier');

    clipboard.on('success', function(e) {
        console.info('Text:', e.text);
        let ipt = parent.document.getElementById('img-link');
        if (ipt != null) ipt.value = e.text;
        e.clearSelection();
    });

    clipboard.on('error', function(e) {
        console.error('Trigger:', e.trigger);
    });
}
function load_popup_hover() {
    $('.copier').popup();
}
function scroll2top() {
    $("html,body").animate({
        scrollTop: 0
    }, 500);
}