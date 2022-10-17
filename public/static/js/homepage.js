let rcm = $("#recommend");
let r_rcm = $("#refresh-recmd");
let lgtb = $("#logo-tab");
let latest = $("#latest-blogs");
function recommend() {
    r_rcm.addClass("loading disabled");
    $.ajax({
        url: "./blog/api.php",
        type: 'POST',
        data: {
            "type": "randomly-select-blogs",
            "amount": 2
        },
        async: true,
        success: function(data) {
            let obj = JSON.parse(data);
            rcm.empty();
            for (let i = 0; i < obj.length; ++i) {
                rcm.append(blog_card_obj_to_str(obj[i], avatar));
            }
            r_rcm.removeClass("loading");
            setTimeout(function () {
                r_rcm.removeClass("disabled");
            }, 2000);
        }
    });
}
let bcs = $("#bcs");
function get_bcs() {
    $.ajax({
        url: "./blog/api.php",
        type: 'POST',
        async: true,
        data: {
            'type': 'get-broadcasts-list'
        },
        success: function(data) {
            console.log(data);
            let obj = JSON.parse(data);
            if (obj['list'].length > 0) $("#bcs-div").show();
            for (let i = 0; i < obj['list'].length; ++i) {
                bcs.append(bc_obj_to_str(obj['list'][i]));
            }
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}
function bc_obj_to_str(obj) {
    let txt = obj['link'] === "/";
    return '' +
        '<tr id="bc' + obj['id'] + '">' +
        '<td>' + (txt ? '' : '<a href="' + obj['link'] + '">') + (obj['stick'] ? '<div class="ui mini red horizontal label">置顶</div>' : '') + obj['title'] + '<div class="ui mini purple horizontal label" style="float: right">' + obj['type'] + '</div>' + (txt ? '' : '</a>') + '</td>' +
        '<td>' + obj['update'] + '</td>' +
        '</tr>';
}
let latest_page = 0;
function latest_blogs() {
    $.ajax({
        url: "/search/api.php",
        type: 'GET',
        data: {
            "type": "blog",
            "keyw": "%",
            "orderby": "latest_edit_time",
            "page": latest_page,
            "desc": "true"
        },
        async: true,
        success: function(data) {
            let json = JSON.parse(data);
            let list = json['list'];
            for (let i = 0; i < list.length; ++i) {
                let obj = list[i];
                latest.append(
                    '<tr><td><a href="/blog/show.php?id=' + obj['id'] + '">' + obj['title'] + '</a>' +
                    ' | By <a href="/user/space.php?uid=' + obj['owner'] + '">'
                    + obj['owner_nickname'] + '</a> | ' + obj['latest_edit_time'] + '</td></tr>');
            }
            if (parseInt(json['cnt']) < 20) {
                latest.append('<tr><td style="background-color: #b4b4b4; text-align: center !important;">已无更多</td></tr>')
            } else {
                latest.append('<tr><td class="load-more" onclick="get_more()">加载更多</td></tr>')
            }
        }
    });
}
function get_more() {
    latest_page++;
    $(".load-more").remove();
    latest_blogs();
}
recommend();
latest_blogs();
get_bcs();
lgtb.transition('scale');