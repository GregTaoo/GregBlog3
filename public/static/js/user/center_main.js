let curpage = 0;
let pages = 0;
function get_blog_list(page) {
    $.ajax({
        url: "./api.php",
        type: 'POST',
        data: {
            "type": "get-blog-list",
            "page": page,
            "uid": uid
        },
        async: true,
            success: function(data) {
            parse_blog_list(data);
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}
function parse_blog_list(data) {
    let obj = JSON.parse(data);
    pages = parseInt((obj['cnt'] - 1) / 20);
    let div = $("#blog-list");
    div.empty();
    for (let i = 0; i < obj['list'].length; ++i) {
        div.append(obj_to_str(obj['list'][i]));
    }
    let ps = $("#page-selector");
    ps.empty();
    ps.append(pages_selector(curpage, pages, (page, num) => '<a onclick="get_blog_list(' + page + ')" class="item">' + num + '</a>'));
}
function obj_to_str(obj) {
    return '' +
        '<tr>' +
        '<td>' + obj['id'] + '</td>' +
        '<td>' +
        '<a href="../blog/show.php?id=' + obj['id'] + '">' +
        obj['title'] +
        '</a>' +
        ' <i class="eye icon"></i> ' + obj['page_view'] +
        '</td>' +
        '<td>' + obj['create_time'] + '</td>' +
        '<td>' + obj['latest_edit_time'] + ' By <a href="./space.php?uid=' + obj['latest_editor'] + '">' + obj['latest_editor_nickname'] + '</a></td>' +
        '<td>' + (obj['visible'] ? "可见" : "不可见") + '</td>' +
        '</tr>'
}
get_blog_list(0);