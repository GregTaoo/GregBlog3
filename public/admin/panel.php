<?php
include '../include.php';
$loader = new Loader("admin-panel");
$loader->init("管理面板");
Loader::add_css("../static/css/menu.css");
$loader->init_end();
if (!User::logged() || !User::verified() || !User::admin()) {
    ?>
    <body>
    <?php $loader->top_menu(); ?>
    <div class="ui main container" style="margin-top: 64px">
        <div class="ui error message" id="error">
            你并不是管理员，You know and so do I.
        </div>
    </div>
    </body>
    <?php
    die;
}
$page = empty($_GET['page']) ? "user" : $_GET['page'];
?>
<body>
<?php $loader->top_menu(); ?>
<div class="ui main container" style="margin-top: 64px">
    <div class="ui secondary pointing menu">
        <a class="item <?php if ($page == "user") echo 'active' ?>" href="?">
            用户
        </a>
        <a class="item <?php if ($page == "blog") echo 'active' ?>" href="?page=blog">
            博客
        </a>
        <a class="item <?php if ($page == "imgur") echo 'active' ?>" href="?page=imgur">
            图片
        </a>
        <a class="item <?php if ($page == "boardcast") echo 'active' ?>" href="?page=boardcast">
            公告
        </a>
        <a class="item <?php if ($page == "site") echo 'active' ?>" href="?page=site">
            网站
        </a>
        <a class="item <?php if ($page == "mail") echo 'active' ?>" href="?page=mail">
            邮件
        </a>
    </div>
    <div id="msg" style="margin-bottom: 20px" hidden>
    </div>
    <?php
    if ($page == "user") { ?>
    <div class="ui form">
        <div class="inline fields">
            <div class="nine wide field">
                <input type="text" id="title-uid" placeholder="授予：UID">
            </div>
            <div class="nine wide field">
                <input type="text" id="title-title" placeholder="头衔">
            </div>
            <div class="two wide field">
                <div class="ui fluid button" onclick="give_title()">授予</div>
            </div>
        </div>
    </div>
    <script>
        function give_title() {
            $.ajax({
                url: "./api.php",
                type: 'POST',
                data: {
                    "manage": "give-title",
                    "uid": $("#title-uid").val(),
                    "title": $("#title-title").val()
                },
                async: true,
                success: function (data) {
                    show(data[0] === '!', data);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseText);
                    show(true, "未知错误");
                }
            });
        }
    </script>
    <?php }
    else if ($page == "imgur") {
        ?>
        <div class="ui segment">
            <div class="ui form">
                <div class="field">
                    指定用户：
                    <input type="text" id="uid" placeholder="UID">
                </div>
                <div class="field">
                    <div class="ui button" onclick="get_imgs(0)">刷新</div>
                </div>
            </div>
            <table class="ui celled table">
                <thead>
                <tr>
                    <th>图片</th>
                    <th>所有者</th>
                    <th>时间</th>
                    <th>大小</th>
                    <th style="width: 120px">操作</th>
                </tr>
                </thead>
                <tbody id="imgs">
                </tbody>
            </table>
            <div id="page-selector"></div>
        </div>
        <script>
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
        </script>
        <?php
    }
    else if ($page == "boardcast") {
        ?>
        <div class="ui segment">
            <div class="ui button" onclick="new_bc()">创建公告</div>
            <table class="ui celled table">
                <thead>
                    <tr>
                        <th>编号</th>
                        <th>标题</th>
                        <th>链接</th>
                        <th>类型</th>
                        <th style="width: 120px">永久置顶</th>
                        <th>创建时间</th>
                        <th>最近置顶</th>
                        <th style="width: 120px">操作</th>
                    </tr>
                </thead>
                <tbody id="bcs">
                </tbody>
            </table>
            <div id="page-selector"></div>
        </div>
        <script>
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
                        'manage': 'delete-boardcast',
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
                        'manage': 'update-boardcast',
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
                        'manage': 'get-boardcasts-list',
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
        </script>
        <?php
    }
    else if ($page == "site") {
        ?>
        <div class="ui form">
            <div class="field">
                <div class="ui primary button" onclick="switch_mod()" id="switch"><i class="sync alternate icon"></i>切换模式</div>
                <div class="ui green button" onclick="update_json()" id="submit"><i class="chevron circle up icon"></i>提交修改</div>
                <div class="ui button" onclick="hide_msg(); get_json()" id="refresh"><i class="sync alternate icon"></i>刷新</div>
            </div>
            <div class="field" id="labels">
            </div>
            <div class="field">
                <div class="ui red button" onclick="set_to_def()" id="s2def"><i class="undo icon"></i>重置为默认</div>
            </div>
        </div>
        <script>
            let subm = $("#submit");
            let swch = $("#switch");
            let refr = $("#refresh");
            let s2def = $("#s2def");
            let labels = $("#labels");
            let json = "";
            let mod = 0;
            let key_list = [];
            function switch_mod() {
                mod = mod === 0 ? 1 : 0;
                get_json();
            }
            function create_labels(obj) {
                labels.empty();
                if (mod === 1) {
                    labels.append('<textarea rows="30" id="label-textarea">' + JSON.stringify(json, null, 4) + '</textarea>');
                } else {
                    let str = '<table class="ui celled table">';
                    for (let key in obj) {
                        str += '<tr><td>' + key + '</td><td><input type="text" value="' + obj[key] + '" id="label-' + key + '"></td></tr>';
                        key_list.push(key);
                    }
                    str += '</table>';
                    labels.append(str);
                }
            }
            function get_json() {
                refr.addClass("loading disabled");
                $.ajax({
                    url: "./api.php",
                    type: 'POST',
                    data: {
                        'manage': 'get-config-array'
                    },
                    async: true,
                    success: function(data) {
                        console.log(data);
                        json = JSON.parse(data);
                        create_labels(json);
                        refr.removeClass("loading");
                        setTimeout(function () {
                            refr.removeClass("disabled")
                        }, 2000);
                    },
                    error:  function(XMLHttpRequest, textStatus, errorThrown) {
                        alert(XMLHttpRequest.responseText);
                    }
                });
            }
            function parse_labels() {
                if (mod === 1) {
                    return $("#label-textarea").val();
                } else {
                    let obj = {};
                    key_list.forEach(
                        (key) => {
                            obj[key] = $("#label-" + key).val();
                            //obj += '"' + key + '":"' + $("#label-" + key).val() + '",';
                        }
                    );
                    return JSON.stringify(obj);
                }
            }
            function update_json() {
                subm.addClass("loading disabled");
                console.log(parse_labels())
                $.ajax({
                    url: "./api.php",
                    type: 'POST',
                    data: {
                        'manage': 'update-config-array',
                        'json': parse_labels()
                    },
                    async: true,
                    success: function(data) {
                        if (data === "success") {
                            show(false, "成功更新了内容");
                            get_json();
                        } else {
                            show(true, data);
                        }
                        subm.removeClass("loading");
                        setTimeout(function () {
                            subm.removeClass("disabled")
                        }, 2000);
                    },
                    error:  function(XMLHttpRequest, textStatus, errorThrown) {
                        alert(XMLHttpRequest.responseText);
                    }
                });
            }
            function set_to_def() {
                s2def.addClass("loading disabled");
                $.ajax({
                    url: "./api.php",
                    type: 'POST',
                    data: {
                        'manage': 'get-config-backup'
                    },
                    async: true,
                    success: function(data) {
                        console.log(data);
                        json = JSON.parse(data);
                        create_labels(json)
                        s2def.removeClass("loading");
                        setTimeout(function () {
                            s2def.removeClass("disabled")
                        }, 2000);
                    },
                    error:  function(XMLHttpRequest, textStatus, errorThrown) {
                        alert(XMLHttpRequest.responseText);
                    }
                });
            }
            get_json();
        </script>
        <?php
    }
    else if ($page == "blog") {
        ?>
        <div class="ui segment">
            <table class="ui celled table">
                <thead>
                <tr>
                    <th>编号</th>
                    <th>作者</th>
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
                        "manage": "get-blog-list",
                        "page": page
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
                    '<td><a href="/user/space.php?uid=' + obj['owner'] + '">' + obj['owner_nickname'] + '</a></td>' +
                    '<td>' +
                    '<a href="../blog/show.php?id=' + obj['id'] + '">' +
                    obj['title'] +
                    '</a>' +
                    ' <i class="eye icon"></i> ' + obj['page_view'] +
                    '</td>' +
                    '<td>' + obj['create_time'] + '</td>' +
                    '<td>' + obj['latest_edit_time'] + ' By <a href="/user/space.php?uid=' + obj['latest_editor'] + '">' + obj['latest_editor_nickname'] + '</a></td>' +
                    '<td>' + (obj['visible'] ? "可见" : "不可见") + '</td>' +
                    '</tr>'
            }
            get_blog_list(0);
        </script>
        <?php
    }
    else if ($page == "mail") {
        ?>
        <div class="ui form">
            <div class="field">
                <label for="mail-test">发送测试邮件：</label>
                <input type="email" id="mail-test" placeholder="xxx@xx.xx">
                <input type="text" id="mail-title" value="Test Title">
                <input type="text" id="mail-body" value="Test Body">
            </div>
            <div class="field">
                <div class="ui primary button" onclick="test_email()">测试</div>
            </div>
        </div>
        <script>
            function test_email() {
                let email = $("#mail-test").val();
                let title = $("#mail-title").val();
                let body = $("#mail-body").val();
                $.ajax({
                    url: "./api.php",
                    type: 'POST',
                    data: {
                        'manage': 'email-test',
                        'email': email,
                        'title': title,
                        'body': body
                    },
                    async: true,
                    success: function(data) {
                        show(data !== "success", data);
                    },
                    error:  function(XMLHttpRequest, textStatus, errorThrown) {
                        alert(XMLHttpRequest.responseText);
                    }
                });
            }
        </script>
        <?php
    }
    ?>
</div>
<?php $loader->footer(); ?>
</body>
<script>
    let msg = $("#msg");
    function show(iserr, str) {
        msg.show();
        msg.empty();
        msg.append(
            '<div class="ui ' + (iserr ? "error" : "success") + ' message">' + str + '</div>'
        )
    }
    function hide_msg() {
        msg.hide();
    }
</script>
<?php
$loader->page_end();