let prog = $("#progress");
let protxt = $("#progress-txt");
function delete_img(md5, id) {
    $.ajax({
        url: "/imgur/api.php?mode=delete&md5=" + md5 + "&id=" + id,
        type: 'GET',
        async: true,
        success: function(data) {
            if (data !== "success") {
                $("#img" + id).addClass("error");
            } else {
                $("#img" + id).addClass("active");
            }
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
            $("#img" + id).addClass("error");
        }
    });
}
let file_input = $("#uploader");
let file_input_btn = $("#uploader-button");
let msgbx = $("#msg");
let path = $("#path");

function check_file_type(name) {
    return ["jpg", "png", "jpeg", "tiff", "webp", "gif", "bmp", "apng"].includes(name.split('.').pop().toLowerCase())
}

let droptarget = document.getElementById("file-upload")
function handleEvent(event) {
    event.preventDefault();
    if (event.type === 'drop') {
        if (event.dataTransfer.files.length > 0) {
            if (check_file_type(event.dataTransfer.files[0].name)) {
                file_input[0].files = event.dataTransfer.files;
                file_input_onchange();
            }
        }
    }
}
droptarget.addEventListener("dragenter", handleEvent);
droptarget.addEventListener("dragover", handleEvent);
droptarget.addEventListener("drop", handleEvent);
droptarget.addEventListener("dragleave", handleEvent);

file_input.change(file_input_onchange);
function file_input_onchange() {
    let file = file_input[0].files[0];
    if (check_file_type(file_input[0].files[0].name) && window.FileReader) {
        let reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onloadend = function (e) {
            $("#show-tmp-img-div").css("display", "block");
            $("#show-tmp-img").attr("src", e.target.result);
            path.empty();
            path.append(file.name);
            process(0);
            protxt.empty();
            refresh_height();
        };
    }
}
document.addEventListener("paste", function (e){
    let cbd = e.clipboardData;
    let ua = window.navigator.userAgent;
    // 如果是 Safari 直接 return
    if ( !(e.clipboardData && e.clipboardData.items) ) {
        return;
    }
    // Mac平台下Chrome49版本以下 复制Finder中的文件的Bug Hack掉
    if(cbd.items && cbd.items.length === 2 && cbd.items[0].kind === "string" && cbd.items[1].kind === "file" &&
        cbd.types && cbd.types.length === 2 && cbd.types[0] === "text/plain" && cbd.types[1] === "Files" &&
        ua.match(/Macintosh/i) && Number(ua.match(/Chrome\/(\d{2})/i)[1]) < 49){
        return;
    }
    if (cbd.items.length === 0) return;
    if (cbd.items[0].kind !== 'file') return;
    let file = cbd.items[0].getAsFile();
    if (file.size <= 0) return;
    file_input[0].files = cbd.files;
    console.log('pasted:' + file.name);
    file_input_onchange();
}, false);
function upload() {
    let file = file_input[0].files[0];
    if (!file) {
        alert("请选择文件！");
        return;
    }
    if (!check_file_type(file_input[0].files[0].name)) {
        alert("不支持的文件格式");
        return;
    }
    let fd = new FormData();
    fd.append("image", file);
    let xhr = new XMLHttpRequest();
    file_input_btn.addClass("disabled loading");
    process(0);
    xhr.open("post", "?step=1", true);
    xhr.upload.onprogress = process_rating;
    xhr.onload = function (e) {
        console.log(e.currentTarget.responseText);
        if (e.currentTarget.responseText[0] === '!') {
            msg(e.currentTarget.responseText);
            return;
        }
        let obj = JSON.parse(e.currentTarget.responseText);
        msg(obj['msg']);
        file_input_btn.removeClass("loading");
        setTimeout(function () {
            file_input_btn.removeClass("disabled");
        }, 5000);
        $("#imgs").prepend(obj_to_str(obj['fb']));
        refresh_height();
    };
    xhr.onerror = function (e) {
        msg(e);
    };
    xhr.send(fd);
}
function process_rating(e) {
    protxt.empty();
    protxt.append(e.loaded * 100 / e.total + ' %');
    if (e.lengthComputable) {
        process(e.loaded * 100 / e.total);
    }
}
function process(x) {
    prog.progress({
        percent: x
    });
}
function msg(e) {
    msgbx.empty();
    msgbx.append(e);
}
function obj_to_str(obj) {
    return '\
            <tr id="img' + obj['id'] + '">\
                <td>\
                    <img src="' + obj['src'] + '" alt="pic" style="max-width: 160px">\
                </td>\
                <td>\
                    <span id="lnk-' + obj['id'] + '">' + obj['src'] + '</span>\
                    <i class="copy icon copier" data-clipboard-target="#lnk-' + obj['id'] + '" onclick="try_insert_img_lnk(' + obj['id'] + ')" data-content="复制成功"></i>\
                </td>\
                <td>' + obj['time'] + '</td>\
                <td>' + (obj['size'] / 1024).toFixed(2) + ' Kib</td>\
                <td>\
                    <div class="ui button" onclick="delete_img(\'' + obj['md5'] + '\',' + obj['id'] + ')">\
                        <i class="trash alternate icon"></i>\
                        删除\
                    </div>\
                </td>\
            </tr>';
}
function try_insert_img_lnk(id) {
    let ipt = parent.document.getElementById('img-link');
    if (ipt != null) ipt.value = $("#lnk-" + id + "-inside").text();
}