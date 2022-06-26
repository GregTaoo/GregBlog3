<?php
include '../include.php';
$loader = new Loader("register");
$loader -> init("注册");
Loader::add_css("../static/css/menu.css");
$loader -> init_end();
?>
<body>
<?php $loader -> top_menu(); ?>
<div class="ui main container" style="margin-top: 64px">
    <div class="ui error message" id="error" hidden></div>
    <div class="ui middle aligned center aligned grid" style="margin-top: 64px">
        <div class="row">
            <div class="column" style="max-width: 450px">
                <h2 class="ui image header">
                    注册
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
                                <i class="user icon"></i>
                                <input name="nickname" placeholder="昵称" type="text" id="nickname">
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
                        <div class="ui fluid large submit primary button" id="register">注册</div>
                    </div>
                </form>
                <a class="ui fluid large basic button" style="margin-top: 20px" href="./login.php">登录</a>
            </div>
        </div>
    </div>
</div>
<?php $loader->footer(); ?>
</body>
<script>
    function show_error(error) {
        $("#error").text(error).show();
    }
    function success() {
        window.location.href = "/user/verify.php";
    }
    function register() {
        let password = $("#password").val();
        if (password !== $("#repassword").val()) {
            show_error("密码重复错误");
            return;
        }
        $("#register").addClass("loading");
        $.ajax({
            url: "./api.php",
            type: 'POST',
            data: {
                "type": "register",
                "email": $("#email").val(),
                "nickname": $("#nickname").val(),
                "password": password
            },
            async: true,
            success: function(data) {
                if (data !== "success") {
                    show_error(data);
                } else {
                    success();
                }
                $("#register").text("注册").removeClass("loading");
            },
            error:  function(XMLHttpRequest, textStatus, errorThrown) {
                alert(XMLHttpRequest.responseText);
                show_error("未知错误");
                $("#register").text("注册");
            }
        });
    }
    $(document).ready(function() {
        $("#register").click(function() {
            register();
        });
    });
</script>
<?php
$loader -> page_end();