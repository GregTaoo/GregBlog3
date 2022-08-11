<?php
include '../include.php';
$loader = new Loader("search");
$loader->init("搜索");
Loader::add_css("../static/css/menu.css");
Loader::add_js("https://unpkg.com/syzoj-public-cdn@1.0.5/cdnjs/blueimp-md5/2.10.0/js/md5.min.js");
$loader->init_end();
$keyw = empty($_GET['keyw']) ? "" : htmlspecialchars($_GET['keyw']);
$config = Info::config();
?>
    <body>
    <?php $loader->top_menu(); ?>
    <div class="ui main container" style="margin-top: 64px">
        <div class="ui segment">
            <div class="ui form">
                <div class="two fields">
                    <div class="field">
                        <div class="ui input">
                            <input type="text" id="keyw" value="<?php echo $keyw; ?>" placeholder="搜点啥吧">
                        </div>
                    </div>
                    <div class="field">
                        <div class="ui primary button" onclick="search(0)" id="btn"><i class="search icon"></i>搜索</div>
                    </div>
                </div>
                排序方式：
                <div class="ui selection dropdown" id="orderby" style="margin-right: 20px">
                    <div class="text">排序方式</div>
                    <i class="dropdown icon"></i>
                </div>
                <div class="ui toggle checkbox">
                    <input type="checkbox" id="desc">
                    <label>从小到大</label>
                </div>
            </div>
        </div>
        <div class="ui pointing menu" id="type-menu">
            <a class="item" id="type-blog" onclick="set_type('blog')">
                博客
            </a>
            <a class="item" id="type-user" onclick="set_type('user')">
                用户
            </a>
        </div>
        <div class="ui segment" id="content-div">
            <div class="ui small header" id="hdr"><?php echo empty($keyw) ? "搜索点啥儿？" : "找不到结果" ?></div>
            <div class="ui divider"></div>
            <div class="ui link two doubling cards" id="content"></div>
            <div id="page-selector"></div>
        </div>
    </div>
    <?php $loader->footer(); ?>
    </body>
    <script>
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
        let def_avatar = <?php echo "'".$config['def_user_avatar']."'" ?>;
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
        <?php if (!empty($keyw)) echo 'search(0);'; ?>
        set_type("blog");
    </script>
<?php
$loader->page_end();