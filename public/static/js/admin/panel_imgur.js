let ps = $("#page-selector");
let curpage = 0, pages = 0;
function delete_img(md5, id) {
    $.ajax({
        url: "../imgur/api.php?mode=delete&md5=" + md5 + "&id=" + id,
        type: 'GET',
        async: true,
        success: function(data) {
            if (data !== "success") {
                $("#img" + id).addClass("error");
            } else {
                $("#img" + id).addClass("active");
            }
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
            $("#img" + id).addClass("error");
        }
    });
}
let imgs = $("#imgs");
function get_imgs(page) {
    $.ajax({
        url: "./api.php",
        type: 'POST',
        async: true,
        data: {
            'manage': 'get-imgur',
            'page': page,
            'uid': $("#uid").val()
        },
        success: function(data) {
            if (data[0] === '!') {
                show(true, data);
            } else {
                let obj = JSON.parse(data);
                imgs.empty();
                ps.empty();
                pages = parseInt((obj['cnt'] - 1) / 20);
                for (let i = 0; i < obj['list'].length; ++i) {
                    imgs.append(obj_to_str(obj['list'][i]));
                }
                ps.append(pages_selector(curpage, pages, (page, num) => '<a onclick="get_imgs(' + page + ')" class="item">' + num + '</a>'));
            }
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}
function obj_to_str(obj) {
    return '' +
        '<tr id="img' + obj['id'] + '">' +
        '<td>' +
        '<img src="' + obj['src'] + '" alt="pic" style="max-width: 160px">' +
        '</td>' +
        '<td><a href="../user/space.php?uid=' + obj['owner'] + '">' + obj['owner_nickname'] + '</a></td>' +
        '<td>' + obj['time'] + '</td>' +
        '<td>' + (obj['size'] / 1024).toFixed(2) + ' Kib</td>' +
        '<td>' +
        '<div class="ui button" onclick="delete_img(\'' + obj['md5'] + '\',' + obj['id'] + ')">' +
        '<i class="trash alternate icon"></i>' +
        '删除' +
        '</div>' +
        '</td>' +
        '</tr>';
}
get_imgs(0);