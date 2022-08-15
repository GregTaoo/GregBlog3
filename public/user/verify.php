<?php
include '../include.php';
$loader = new Loader("verify");
$loader->init("验证邮箱");
Loader::add_css("../static/css/menu.css");
$loader->init_end();
$error = "您尚未登录";
$success = false;
$is_check = !empty($_GET['code']);
if (User::logged()) {
    $user = new User($loader->info->conn);
    $user->uid = $_SESSION['uid'];
    $user->personal = true;
    $user->query("uid");
    if ($user->exist) {
        if ($is_check) {
            if (time() - $user->verify_time <= 300) {
                if ($_GET['code'] == $user->verify_code && $user->set_to_verified()) {
                    $error = "您的邮箱已经成功被验证";
                    $_SESSION['verified'] = true;
                    $success = true;
                } else $error = "验证码错误或不存在";
            } else $error = "验证码已经超时，请重试并在5分钟内完成验证";
        } else {
            if (!$user->verified) {
                if (time() - $user->verify_time > 60) {
                    if ($user->send_verify_code()) {
                        $error = "成功发送了邮件，请及时查收并点击链接验证";
                        $success = true;
                    } else $error = "邮件发送失败";
                } else $error = "请一分钟后再访问该界面重试，您一分钟以内已经尝试让我们发送过验证邮件";
            } else $error = "你已经验证过邮箱";
        }
    } else $error = "用户不存在";
}
?>
<body>
<?php $loader->top_menu(); ?>
    <div class="ui main container" style="margin-top: 64px; ">
        <div class="ui icon <?php echo $success ? "success" : "error"?> message">
            <i class="<?php echo $success ? "icon checkmark" : "exclamation triangle icon"?>"></i>
            <div class="content">
                <div class="header">
                    提示
                </div>
                <p><?php echo $error ?></p>
            </div>
        </div>
    </div>
    <?php $loader->footer(); ?>
</body>
<?php
$loader->page_end();