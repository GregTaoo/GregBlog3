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
            let uid = <?php echo User::uid(); ?>;
        </script>
        <script src="../static/js/user/center_main.js">
        </script>
        <?php }
        else if ($page == "message") {
            ?>
            <div class="ui very relaxed list" id="messages">
            </div>
            <script>
                let avatar = <?php echo "'".$config['def_user_avatar']."'" ?>;
            </script>
            <script src="../static/js/user/center_message.js">
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
            <script src="../static/js/user/center_collection.js"></script>
            <?php
        }
        ?>
    </div>
    <?php $loader->footer(); ?>
    </body>
<?php
$loader->page_end();