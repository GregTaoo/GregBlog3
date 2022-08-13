function test_email() {
    let email = $("#mail-test").val();
    let title = $("#mail-title").val();
    let body = $("#mail-body").val();
    $.ajax({
        url: "./api.php",
        type: 'POST',
        data: {
            'manage': 'email-test',
            'email': email,
            'title': title,
            'body': body
        },
        async: true,
        success: function(data) {
            show(data !== "success", data);
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}