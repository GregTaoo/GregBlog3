function give_title() {
    $.ajax({
        url: "./api.php",
        type: 'POST',
        data: {
            "manage": "give-title",
            "uid": $("#title-uid").val(),
            "title": $("#title-title").val()
        },
        async: true,
        success: function (data) {
            show(data[0] === '!', data);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
            show(true, "未知错误");
        }
    });
}
function ban_user() {
    $.ajax({
        url: "./api.php",
        type: 'POST',
        data: {
            "manage": "ban-user",
            "uid": $("#ban-uid").val(),
            "time": $("#ban-time").val()
        },
        async: true,
        success: function (data) {
            show(data[0] === '!', data);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
            show(true, "未知错误");
        }
    });
}