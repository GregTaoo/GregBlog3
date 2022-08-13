let ctt = $("#content");
let ctthdr = $("#hdr");
let cttdiv = $("#content-div");
let ordby = $("#orderby");
let keyw = $("#keyw");
keyw.keydown(function (e){
    if (e.keyCode === 13) search();
});
let btn = $("#btn");
let pgs = $("#page-selector");
let curpage = 0, pages = 0;
let type = 'blog';
ordby.dropdown({
    values: [
        {
            name: '访问量',
            value: 'page_view',
            selected: true
        },
        {
            name: '博客ID',
            value: 'id'
        },
        {
            name: '创建时间',
            value: 'create_time'
        },
        {
            name: '编辑时间',
            value: 'latestedit_time'
        },
        {
            name: '回复数量',
            value: 'replies_sum'
        }
    ]
})
;
function search(page) {
    if (keyw.val().length === 0) {
        return;
    }
    btn.addClass("loading disabled");
    cttdiv.addClass("loading");
    $.ajax({
        url: "./api.php",
        type: 'GET',
        data: {
            "type": type,
            "page": page,
            "keyw": keyw.val(),
            "orderby": ordby.dropdown('get value'),
            "desc": !$("#desc").prop("checked")
        },
        async: true,
        success: function(data) {
            console.log(data);
            curpage = pages = 0;
            let obj = JSON.parse(data);
            ctthdr.empty();
            if (obj['cnt'] === "0") ctthdr.append('找不到结果');
            else ctthdr.append('共 ' + obj['cnt'] + ' 条结果');
            pages = parseInt(parseInt(obj['cnt']) / 20);
            ctt.empty();
            for (let i = 0; i < obj['list'].length; ++i) {
                ctt.append(
                    () => {
                        switch (type) {
                            case "blog": return blog_card_obj_to_str(obj['list'][i], def_avatar);
                            case "user": return user_card_obj_to_str(obj['list'][i], def_avatar);
                        }
                    }
                );
            }
            pgs.empty();
            pgs.append(pages_selector(curpage, pages, (page, num) => '<a onclick="search(' + page + ')" class="item">' + num + '</a>'));
            btn.removeClass("loading");
            cttdiv.removeClass("loading");
            setTimeout(btn.removeClass("disabled"), 2000);
            let num = "two";
            switch (type) {
                case "blog": {
                    num = "two";
                    break;
                }
                case "user": {
                    num = "six";
                    break;
                }
            }
            ctt.removeClass();
            ctt.addClass("ui link " + num + " doubling cards blog");
        }
    });
}
function set_type(typ) {
    $("#type-" + type).removeClass("active");
    type = typ;
    $("#type-" + typ).addClass("active");
    search(0);
}
set_type("blog");