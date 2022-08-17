<?php
include '../include.php';
$loader = new Loader("forgetpw");
$loader->init("密码找回");
Loader::add_css("../static/css/menu.css");
$loader->init_end();
$step = empty($_GET['step']) ? 0 : $_GET['step'];
function show_error($msg) {
    echo '<script>
        let err = $("#error");
        err.empty();
        err.append("'.$msg.'")
        err.show();
    </script>';
}
?>
    <body>
    <?php $loader->top_menu(); ?>
    <div class="ui main container" style="margin-top: 64px">
        <div class="ui error message" id="error" <?php echo (empty($_GET['alert']) ? "hidden>" : ">".$_GET['alert']); ?></div>
        <?php
        if (User::logged()) {
            show_error("你已经登录");
            ?>
            </div>
            <?php
            $loader->footer();
            die;
        }
        ?>
        <div class="ui middle aligned center aligned grid" style="margin-top: 64px">
            <div class="row">
                <div class="column" style="max-width: 450px">
                    <h2 class="ui image header">
                        密码找回
                    </h2>
                    <?php
                    if ($step == 0) {
                    ?>
                    <form class="ui large form" method="post" action="?step=1">
                        <div class="ui existing segment">
                            <div class="field">
                                <div class="ui left icon input">
                                    <i class="envelope icon"></i>
                                    <input name="email" placeholder="邮箱" type="text" id="email">
                                </div>
                            </div>
                            <div class="field">
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
                                <div class="ui left icon input">
                                    <i class="edit icon"></i>
                                    <input name="reason" placeholder="原因" type="text" id="reason">
                                </div>
                            </div>
                            <input class="ui fluid large submit primary button" type="submit" value="提交">
                        </div>
                    </form>
                    <?php
                    } else if ($step == 1) {
                        $email = $_POST['email'];
                        $password = $_POST['password'];
                        $repassword = $_POST['repassword'];
                        $reason = $_POST['reason'];
                        if (!is_tinytext($reason) || empty($reason)) {
                            show_error("请填写256字符以内的非空申请原因！");
                        } else if (!is_tinytext($email) || empty($email) || !preg_match("/[\w\-_]+@[\w\-_]+.\w+/", $email)) {
                            show_error("邮箱格式错误");
                        } else {
                            $user = User::get_user_by_email($loader->info->conn, $email, false);
                            if (!$user->exist) show_error("用户不存在");
                            else if (!check_length($password, 6, 100)) show_error("请填写密码（长度6~100）");
                            else if ($password != $repassword) show_error("重复密码与所填密码不一致");
                            else {
                                $last = ForgetPw::user_get_last_record($loader->info->conn, $user->uid);
                                if (!$last->exist || time() - $last->timestamp >= 60) {
                                    $config = Info::config();
                                    $forgetpw = ForgetPw::create($loader->info->conn, $user->uid, $password, $reason);
                                    if (!Mail::send_email($user->email, "重置您的密码",
                                        '<h3>您正在请求修改密码：'.$forgetpw->reason.'</h3>点击链接以修改：
                                           <a href="' . get_url_prefix() . $config['domain'] . '/user/forgetpw.php?step=2&id=' . $forgetpw->id . '&code=' . $forgetpw->code . '">链接</a>', $err)) {
                                        show_error("邮件发送失败");
                                    } else {
                                        ?>
                                        <div class="ui success message">邮件发送成功，请尽快点击验证</div>
                                        <?php
                                    }
                                } else {
                                    show_error("请等待60秒后重试");
                                }
                            }
                        }
                    } else if ($step == 2) {
                        if (empty($_GET['id']) || empty($_GET['code'])) {
                            show_error("未知的id");
                        } else {
                            $id = $_GET['id'];
                            $code = $_GET['code'];
                            $forgetpw = ForgetPw::get_by_id($loader->info->conn, $id);
                            if (!$forgetpw->exist) show_error("不存在的id");
                            else if ($forgetpw->code != $code) show_error("密钥错误");
                            else if (time() - $forgetpw->timestamp > 300) show_error("请重新发起请求，该密钥已经过期");
                            else {
                                ?>
                                <form class="ui large form" method="post" action="?step=3<?php echo '&id='.$id.'&code='.$code ?>">
                                    <div class="ui existing segment">
                                        <div class="field">
                                            <div class="ui left icon input">
                                                <i class="lock icon"></i>
                                                <input name="password" placeholder="再次输入新密码" type="password" id="password">
                                            </div>
                                        </div>
                                        <input class="ui fluid large submit primary button" type="submit" value="提交">
                                    </div>
                                </form>
                                <?php
                            }
                        }
                    } else if ($step == 3) {
                        if (empty($_GET['id']) || empty($_GET['code']) || empty($_POST['password'])) {
                            show_error("未知的id");
                        } else {
                            $id = $_GET['id'];
                            $code = $_GET['code'];
                            $password = $_POST['password'];
                            $forgetpw = ForgetPw::get_by_id($loader->info->conn, $id);
                            if (!$forgetpw->exist) show_error("不存在的id");
                            else if ($forgetpw->code != $code) show_error("密钥错误");
                            else if (!password_verify($password, $forgetpw->password)) show_error("重复密码错误，请重试");
                            else if (time() - $forgetpw->timestamp > 300) show_error("请重新发起请求，该密钥已经过期");
                            else {
                                $user = User::get_user_by_uid($loader->info->conn, $forgetpw->uid, true);
                                $user->password = $forgetpw->password;
                                if (!$user->update_simply() || !$forgetpw->set_checked()) show_error("数据库错误");
                                else {
                                    redirect("/redirect.php?url=/user/login.php&msg=密码修改完毕");
                                }
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php $loader->footer(); ?>
    </body>
    <script>
        let from_url = <?php echo "'".get_url_prefix()."'" ?>;
    </script>
    <script src="../static/js/user/login.js">
    </script>
<?php
$loader->page_end();