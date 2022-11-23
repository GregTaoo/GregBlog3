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
        <a class="item <?php if ($page == "broadcast") echo 'active' ?>" href="?page=broadcast">
            公告
        </a>
        <a class="item <?php if ($page == "site") echo 'active' ?>" href="?page=site">
            网站设置
        </a>
        <a class="item <?php if ($page == "emotion") echo 'active' ?>" href="?page=emotion">
            表情
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
    <div class="ui form">
        <div class="inline fields">
            <div class="nine wide field">
                <input type="text" id="ban-uid" placeholder="封禁：UID">
            </div>
            <div class="nine wide field">
                <input type="text" id="ban-time" placeholder="时长：秒">
            </div>
            <div class="two wide field">
                <div class="ui fluid red button" onclick="ban_user()">封禁</div>
            </div>
        </div>
    </div>
    <script src="../static/js/admin/panel_user.js">
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
        <script src="../static/js/admin/panel_imgur.js">
        </script>
        <?php
    }
    else if ($page == "broadcast") {
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
        <script src="../static/js/admin/panel_broadcast.js">
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
        <script src="../static/js/admin/panel_site.js">
        </script>
        <?php
    }
    else if ($page == "emotion") {
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
        <script src="../static/js/admin/panel_emotion.js">
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
        <script src="../static/js/admin/panel_blog.js">
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
        <script src="../static/js/admin/panel_mail.js">
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