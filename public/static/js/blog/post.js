let cp = $("#color-picker");
cp.val('#000000');
let cptxt = $("#picker-rbg");
cp.on('input', function () {
    cptxt.empty();
    cptxt.append(cp.val());
});

let acc = $("#accordion");
acc.accordion({
    selector: {
        trigger: '#acc-title'
    }
});

let editor = CodeMirror.fromTextArea(document.getElementById("text"),{
    lineNumbers: true,//显示行号
    mode: "text/markdown",  // 模式，这里指定html
    theme: "<?php echo $config['codemirror_theme']; ?>"
});

let picker_div = $("#pick-color");
let imgur = $("#imgur");
let imgur_div = $("#imgur-div");
let add_img = $("#add-img");
let add_lnk = $("#add-lnk");
let add_code = $("#add-code");
let code_pkr = $('#code-picker');
code_pkr.dropdown();

function show_error(error) {
    $("#error").text(error).show();
}
function success(data) {
    window.location.href = "./show.php?id=" + data.substring(7, data.length);
}
function submit() {
    $("#submit").addClass("loading");
    editor.save();
    $.ajax({
        url: "./api.php",
        type: 'POST',
        data: {
            "id": is_edit ? id : "",
            "type": type,
            "editors": $("#editors").val(),
            "title": $("#title").val(),
            "visible": $("#visible").prop("checked") ? "不可见" : "可见",
            "text": $('<div>').text($("#text").val()).html(),
            "intro": $("#intro").val(),
            "tags": $("#tags").val()
        },
        async: true,
        success: function(data) {
            if (data[0] !== "s") {
                show_error(data);
            } else {
                success(data);
            }
            $("#submit").text("发帖").removeClass("loading");
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
            show_error("未知错误");
            $("#submit").text("发帖");
        }
    });
}
$(document).ready(function() {
    $("#submit").click(function() {
        submit();
    });
});