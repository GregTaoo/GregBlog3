function show_error(error) {
    $("#error").text(error).show();
}
function success() {
    redirect_to_from(from_url);
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