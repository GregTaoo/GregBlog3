function show_error(error) {
    $("#error").text(error).show();
}
function manage() {
    let password = $("#password").val();
    if (password !== $("#repassword").val()) {
        show_error("密码重复错误");
        return;
    }
    let change_pw = password.length > 0;
    $("#manage").addClass("loading");
    $.ajax({
        url: "./api.php",
        type: 'POST',
        data: {
            "type": "manage",
            "nickname": $("#nickname").val(),
            "password": password,
            "intro": $("#intro").val(),
            'allow_be_srch': !$("#visible").prop("checked")
        },
        async: true,
        success: function(data) {
            if (data !== "success") {
                show_error(data);
            } else {
                window.location.href = change_pw ? "./login.php?from=" + from_url_encoded + "&alert=密码已修改，请重新登录&" : "./center.php";
            }
            $("#manage").text("提交修改").removeClass("loading");
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
            show_error("未知错误");
            $("#manage").text("提交修改");
        }
    });
}
$(document).ready(function() {
    $("#manage").click(function() {
        manage();
    });
});