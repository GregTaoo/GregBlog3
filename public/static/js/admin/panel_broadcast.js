let ps = $("#page-selector");
let curpage = 0, pages = 0;
function delete_bc(id) {
    if (id === 0) {
        $("#bc0").remove();
        return;
    }
    $.ajax({
        url: "./api.php",
        type: 'POST',
        async: true,
        data: {
            'manage': 'delete-broadcast',
            'id': id
        },
        success: function(data) {
            if (data !== "success") {
                $("#bc" + id).addClass("error");
            } else {
                $("#bc" + id).addClass("active");
            }
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
            $("#bc" + id).addClass("error");
        }
    });
}
function update_bc(id) {
    let edit = id !== 0;
    $.ajax({
        url: "./api.php",
        type: 'POST',
        async: true,
        data: {
            'manage': 'update-broadcast',
            'id': id,
            'edit': edit,
            'title': $("#bc" + id + "-title").val(),
            'type': $("#bc" + id + "-type").val(),
            'link': $("#bc" + id + "-link").val(),
            'stick': $("#bc" + id + "-stick").prop("checked")
        },
        success: function(data) {
            console.log(data);
            let div = $("#bc" + id);
            if (data === "failed") {
                div.addClass("error");
            } else if (edit) {
                div.replaceWith(obj_to_str(JSON.parse(data)));
                div.addClass("success");
            } else {
                div.remove();
                bcs.prepend(obj_to_str(JSON.parse(data)));
            }
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
            $("#bc" + id).addClass("error");
        }
    });
}
let bcs = $("#bcs");
function get_bcs(page) {
    $.ajax({
        url: "./api.php",
        type: 'POST',
        async: true,
        data: {
            'manage': 'get-broadcasts-list',
            'page': page
        },
        success: function(data) {
            console.log(data);
            let obj = JSON.parse(data);
            bcs.empty();
            ps.empty();
            pages = parseInt((obj['cnt'] - 1) / 20);
            for (let i = 0; i < obj['list'].length; ++i) {
                bcs.append(obj_to_str(obj['list'][i]));
            }
            ps.append(pages_selector(curpage, pages, (page, num) => '<a onclick="get_bcs(' + page + ')" class="item">' + num + '</a>'));
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}
function new_bc() {
    $("#bc0").remove();
    bcs.prepend(obj_to_str({
        'id': 0,
        'title': '',
        'link': '',
        'type': '',
        'time': 'New',
        'update': 'New',
        'stick': false
    }));
}
function obj_to_str(obj) {
    return '' +
        '<tr id="bc' + obj['id'] + '">' +
        '<td>' +
        obj['id'] +
        '</td>' +
        '<td><div class="ui input"><input type="text" id="bc' + obj['id'] + '-title" value="' + obj['title'] + '" placeholder="标题"></div></td>' +
        '<td><div class="ui input"><input type="text" id="bc' + obj['id'] + '-link" value="' + obj['link'] + '" placeholder="链接"></div></td>' +
        '<td><div class="ui input"><input type="text" id="bc' + obj['id'] + '-type" value="' + obj['type'] + '" placeholder="类型"></div></td>' +
        '<td>' +
        '<div class="ui toggle checkbox">' +
        '<input type="checkbox" id="bc' + obj['id'] + '-stick" ' + (obj['stick'] ? 'checked="checked"' : '') + '>' +
        '<label>置顶</label>' +
        '</div>' +
        '</td>' +
        '<td>' + obj['time'] + '</td>' +
        '<td>' + obj['update'] + '</td>' +
        '<td>' +
        '<div class="ui button" onclick="delete_bc(' + obj['id'] + ')">' +
        '<i class="trash alternate icon"></i>' +
        '删除' +
        '</div>' +
        '<div class="ui primary button" onclick="update_bc(' + obj['id'] + ')">' +
        '<i class="paper plane icon"></i>' +
        '更新' +
        '</div>' +
        '</td>' +
        '</tr>';
}
get_bcs(0);