<?php
include '../include.php';
$loader = new Loader("login");
$loader->init("登录");
Loader::add_css("../static/css/menu.css");
$loader->init_end();
?>
    <body>
    <?php $loader->top_menu(); ?>
    <div class="ui main container" style="margin-top: 64px">
        <div class="ui error message" id="error" <?php echo User::logged() ? ">你已经登录过" : (empty($_GET['alert']) ? "hidden>" : ">".$_GET['alert']); if (User::logged()) die;?>
        </div>
        <div class="ui middle aligned center aligned grid" style="margin-top: 64px">
            <div class="row">
                <div class="column" style="max-width: 450px">
                    <h2 class="ui image header">
                        登录
                    </h2>
                    <form class="ui large form">
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
                                <div class="ui toggle checkbox">
                                    <input type="checkbox" id="auto-login">
                                    <label>自动登录</label>
                                </div>
                            </div>
                            <div class="ui fluid large submit primary button" id="login">登录</div>
                        </div>
                    </form>
                    <div class="ui fluid large basic buttons" style="margin-top: 20px">
                        <a href="./register.php" class="ui button">注册</a>
                        <a href="./forgetpw.php" class="ui button">忘记密码</a>
                    </div>
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