let rcm = $("#recommend");
let r_rcm = $("#refresh-recmd");
let lgtb = $("#logo-tab");
function recommend() {
    r_rcm.addClass("loading disabled");
    $.ajax({
        url: "./blog/api.php",
        type: 'POST',
        data: {
            "type": "randomly-select-blogs",
            "amount": 4
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
recommend();
get_bcs();
lgtb.transition('scale');