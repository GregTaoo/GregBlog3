<?php
include '../include.php';
$uid = empty($_GET['uid']) ? 0 : $_GET['uid'];
$loader = new Loader("space");
$user = User::get_user_by_uid($loader->info->conn, $uid, false);
$loader->init($user->exist ? $user->nickname."的空间" : "该用户不存在");
Loader::add_css("../static/css/menu.css");
$loader->init_end();
if (!$user->exist) {
    ?>
    <body>
    <?php $loader->top_menu(); ?>
    <div class="ui main container" style="margin-top: 64px">
        <div class="ui error message" id="error">
            该用户不存在！
        </div>
    </div>
    </body>
    <?php
    die;
}
?>
    <body>
    <?php $loader->top_menu(); ?>
    <div class="ui main container" style="margin-top: 64px">
        <?php if (User::logged() && $uid == User::uid()) echo '<div class="ui message">你正在其他用户视角中，点击<a href="/user/center.php">返回用户中心</a></div>' ?>
        <div class="ui horizontal segments" style="overflow: hidden">
            <div class="ui segment">
                <h2 class="ui header" style="text-align: center">
                    <?php echo $user->get_avatar_img(); ?>
                    <div class="sub header">
                        <?php echo $user->nickname.($user->admin ? User::get_admin_label() : "").User::get_title_label($loader->info->conn, $user); ?>
                        <br>
                        UID: <?php echo $user->uid ?><br>
                        <a href="mailto:<?php echo $user->email; ?>"><?php echo $user->email; ?></a>
                    </div>
                </h2>
            </div>
            <div class="ui segment">
                <?php if ($user->be_banned()) echo '<strong style="color:red">该用户封禁中</strong><br>' ?>
                个性签名：<?php echo $user->intro; ?>
            </div>
        </div>
        <div class="ui segment">
            <table class="ui celled table">
                <thead>
                    <tr>
                        <th>编号</th>
                        <th>标题</th>
                        <th>发布时间</th>
                        <th>最后编辑</th>
                    </tr>
                </thead>
                <tbody id="blog-list">
                </tbody>
            </table>
            <div id="page-selector"></div>
        </div>
    </div>
    <script>
        let uid = <?php echo $uid; ?>;
    </script>
    <script src="../static/js/user/space.js">
    </script>
    <?php $loader->footer(); ?>
    </body>
<?php
$loader->page_end();