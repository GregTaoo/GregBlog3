let ctt = $("#content");
let cttdiv = $("#content-div");
let pgs = $("#page-selector");
let hdrdiv = $("#header-div");
let curpage = 0, pages = 0;
function query(page) {
    cttdiv.addClass("loading");
    $.ajax({
        url: "./api.php",
        type: 'POST',
        data: {
            "type": "get-collections",
            "page": page
        },
        async: true,
        success: function(data) {
            console.log(data);
            curpage = pages = 0;
            let obj = JSON.parse(data);
            pages = parseInt(parseInt(obj['cnt']) / 20);
            hdrdiv.empty();
            hdrdiv.append("共 " + obj['cnt'] + " / <?php echo $config['collection_size'] ?> 条收藏");
            if (obj['cnt'] === 0) {
                cttdiv.hide();
                return;
            }
            ctt.empty();
            for (let i = 0; i < obj['list'].length; ++i) {
                ctt.append(collected_blog_card_obj_to_str(obj['list'][i]));
            }
            pgs.empty();
            pgs.append(pages_selector(curpage, pages, (page, num) => '<a onclick="query(' + page + ')" class="item">' + num + '</a>'));
            cttdiv.removeClass("loading");
        }
    });
}
function dis_collect(blogid) {
    $.ajax({
        url: "../user/api.php",
        type: 'POST',
        data: {
            "type": "dis-collect",
            "blogid": blogid
        },
        async: true,
        success: function(data) {
            console.log(data);
            let div = $("#clt-" + blogid);
            if (data === 'success') {
                div.remove();
            } else {
                alert(data);
            }
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}
query(0);