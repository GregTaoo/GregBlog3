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
                    <a class="ui fluid large basic button" style="margin-top: 20px" href="./register.php">注册</a>
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
            redirect_to_from(<?php echo "'".get_url_prefix()."'" ?>);
        }
        function login() {
            let password = $("#password").val();
            let fd = new FormData();
            fd.append("password", password);
            fd.append("email", $("#email").val());
            fd.append("auto-login", $("#auto-login").prop("checked"));
            fd.append("type", "login");
            $("#login").addClass("loading");
            $.ajax({
                url: "./api.php",
                type: 'POST',
                data: fd,
                contentType: false,
                processData: false,
                async: true,
                success: function(data) {
                    if (data !== "success") {
                        show_error(data);
                    } else {
                        success();
                    }
                    $("#login").text("登录").removeClass("loading");
                },
                error:  function(XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseText);
                    show_error("未知错误");
                    $("#login").text("登录");
                }
            });
        }
        $(document).ready(function() {
            $("#login").click(function() {
                login();
            });
        });
    </script>
<?php
$loader->page_end();