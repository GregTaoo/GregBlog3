let curpage = 0;
let pages = 0;
function get_msgs(page) {
    $.ajax({
        url: "./api.php",
        type: 'POST',
        data: {
            "type": "get-message",
            "page": page
        },
        async: true,
        success: function(data) {
            parse_msgs(data);
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}
function parse_msgs(data) {
    console.log(data)
    let obj = JSON.parse(data);
    pages = parseInt((obj['cnt'] - 1) / 20);
    let div = $("#messages");
    div.empty();
    for (let i = 0; i < obj['list'].length; ++i) {
        div.append(obj_to_str(obj['list'][i]));
    }
    div.append(pages_selector(curpage, pages, (page, num) => '<a onclick="get_msgs(' + page + ')" class="item">' + num + '</a>'));
}
function obj_to_str(obj) {
    let href = 'href="./space.php?uid=' + obj['from'] + '"';
    return '\
        <div class="item">\
        <img class="ui avatar image" src="https://cravatar.cn/avatar/' + obj['from_emmd5'] + '?d=' + avatar + '" alt="avatar">\
        <div class="content">\
        <a class="header" ' + href + '>' + obj['from_nickname'] + '</a>\
        <div class="description">' + obj['text'] + ' -- <span style="color: grey">' + obj['time'] + '</span></div>\
        </div>\
        </div>';
}
get_msgs(0);