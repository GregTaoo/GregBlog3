<?php
include '../include.php';
$loader = new Loader("manage");
$loader->init("资料修改");
Loader::add_css("../static/css/menu.css");
$loader->init_end();
if (!User::logged()) {
    echo_error_body($loader,"你尚未登录");
    die;
}
$uid = empty($_SESSION['uid']) ? 0 : $_SESSION['uid'];
$user = User::get_user_by_uid($loader->info->conn, $uid, true);
if (!$user->exist) {
    echo_error_body($loader, "该用户不存在");
    die;
}
?>
    <body>
    <?php $loader->top_menu(); ?>
    <div class="ui main container" style="margin-top: 64px">
        <div class="ui error message" id="error" hidden></div>
        <div class="ui middle aligned center aligned grid" style="margin-top: 64px">
            <div class="row">
                <div class="column" style="max-width: 640px">
                    <h2 class="ui image header">
                        资料修改
                    </h2>
                    <form class="ui large form">
                        <div class="ui existing segment">
                            <div class="field">
                                <div class="ui left icon input">
                                    <i class="user icon"></i>
                                    <input name="nickname" placeholder="昵称" type="text" id="nickname" value="<?php echo $user->nickname ?>">
                                </div>
                            </div>
                            <div class="field">
                                <div class="ui pointing below label">
                                    不填写即为不修改密码
                                </div>
                                <div class="ui left icon input">
                                    <i class="lock icon"></i>
                                    <input name="password" placeholder="密码" type="password" id="password">
                                </div>
                            </div>
                            <div class="field">
                                <div class="ui left icon input">
                                    <i class="lock icon"></i>
                                    <input name="repassword" placeholder="重复密码" type="password" id="repassword">
                                </div>
                            </div>
                            <div class="field">
                                <div class="ui pointing below label">
                                    个性签名，不填写则会重置为默认
                                </div>
                                <textarea id="intro" name="intro"><?php echo $user->intro ?></textarea>
                            </div>
                            <div class="field">
                                <div class="ui toggle checkbox">
                                    <input type="checkbox" id="visible" <?php if (!$user->allow_be_srch) echo 'checked="checked"' ?>>
                                    <label>不允许被搜索</label>
                                </div>
                            </div>
                            <div class="ui fluid large submit primary button" id="manage">提交修改</div>
                        </div>
                    </form>
                    <a class="ui fluid large basic button" style="margin-top: 20px" href="./center.php">返回个人空间</a>
                </div>
            </div>
        </div>
    </div>
    <?php $loader->footer(); ?>
    </body>
    <script>
        let from_url_encoded = <?php echo "'".get_full_cur_url_encoded()."'" ?>;
    </script>
    <script src="../static/js/user/manage.js">
    </script>
<?php
$loader->page_end();