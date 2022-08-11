<?php
include '../include.php';
$loader = new Loader("user-center");
$loader->init(User::logged() ? "个人中心" : "你尚未登录");
$config = Info::config();
Loader::add_css("../static/css/menu.css");
$loader->init_end();
$uid = empty($_SESSION['uid']) ? 0 : $_SESSION['uid'];
if (!User::logged()) {
    echo_error_body($loader, "你尚未登录");
    redirect("./login.php?alert=你尚未登录&from=".get_full_cur_url_encoded());
    die;
}
$user = User::get_user($loader->info->conn, $uid, true);
if (!$user->exist) {
    echo_error_body($loader, "用户UID".$uid."不存在");
    die;
}
$page = empty($_GET['page']) ? "main" : $_GET['page'];
?>
    <body>
    <?php $loader->top_menu(); ?>
    <div class="ui main container" style="margin-top: 64px">
        <div class="ui secondary pointing menu">
            <a class="item <?php if ($page == "main") echo 'active' ?>" href="?">
                主面板
            </a>
            <a class="item <?php if ($page == "message") echo 'active' ?>" href="?page=message">
                消息中心<?php echo $loader->msgs > 0 ? '<div class="ui red label">'.$loader->msgs.'</div>' : '';?>
            </a>
            <a class="item <?php if ($page == "collection") echo 'active' ?>" href="?page=collection">
                收藏夹
            </a>
        </div>
        <?php
        if ($page == "main") { ?>
        <div class="ui horizontal segments" style="overflow: hidden">
            <div class="ui segment">
                <h2 class="ui header" style="text-align: center">
                    <?php echo $user->get_avatar_img(); ?>
                    <div class="sub header">
                        <?php echo $user->nickname.($user->admin ? User::get_admin_label() : "").User::get_title_label($loader->info->conn, $user);; ?>
                        <br>
                        UID: <?php echo $user->uid ?><br>
                        <a href="mailto:<?php echo $user->email; ?>"><?php echo $user->email; ?></a>
                    </div>
                </h2>
            </div>
            <div class="ui segment">
                个性签名：<?php echo $user->intro; ?>
                <br>
                注册时间：<?php echo $user->regtime; ?>
            </div>
        </div>
        <div class="ui segment">
            <a href="https://cravatar.cn/emails" class="ui button">
                <i class="user circle icon"></i>
                更换头像（站外）
            </a>
            <a href="./manage.php" class="ui blue button">
                <i class="address card icon"></i>
                修改资料
            </a>
            <a href="../imgur/upload.php" class="ui blue button">
                <i class="file image icon"></i>
                管理图床
            </a>
            <?php if ($user->admin) { ?>
            <a href="../admin/panel.php" class="ui teal button">
                <i class="user plus icon"></i>
                管理员面板
            </a>
            <?php } ?>
        </div>
        <div class="ui segment">
            <table class="ui celled table">
                <thead>
                <tr>
                    <th>编号</th>
                    <th>标题</th>
                    <th>发布时间</th>
                    <th>最后编辑</th>
                    <th>状态</th>
                </tr>
                </thead>
                <tbody id="blog-list">
                </tbody>
            </table>
            <div id="page-selector">
            </div>
        </div>
        <script>
            let curpage = 0;
            let pages = 0;
            function get_blog_list(page) {
                $.ajax({
                    url: "./api.php",
                    type: 'POST',
                    data: {
                        "type": "get-blog-list",
                        "page": page,
                        "uid": <?php echo User::uid(); ?>
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
        </script>
        <?php }
        else if ($page == "message") {
            ?>
            <div class="ui very relaxed list" id="messages">
            </div>
            <script>
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
                    return '' +
                        '<div class="item">' +
                        '<img class="ui avatar image" src="https://cravatar.cn/avatar/' + obj['from_emmd5'] + '?d=<?php echo $config['def_user_avatar'] ?>" alt="avatar">' +
                        '<div class="content">' +
                        '<a class="header" ' + href + '>' + obj['from_nickname'] + '</a>' +
                        '<div class="description">' + obj['text'] + ' -- <span style="color: grey">' + obj['time'] + '</span></div>' +
                        '</div>' +
                        '</div>';
                }
                get_msgs(0);
            </script>
            <?php
        }
        else if ($page == "collection") {
            ?>
            <div class="ui segment" id="header-div">
            </div>
            <div class="ui segment" id="content-div">
                <div class="ui link two doubling cards" id="content"></div>
                <div id="page-selector"></div>
            </div>
            <script>
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
            </script>
            <?php
        }
        ?>
    </div>
    <?php $loader->footer(); ?>
    </body>
<?php
$loader->page_end();