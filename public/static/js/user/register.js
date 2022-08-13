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