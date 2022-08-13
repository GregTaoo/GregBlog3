<?php
include '../include.php';
$loader = new Loader("upload-img");
$config = Info::config();
$step = empty($_GET['step']) ? 0 : $_GET['step'];
$iframe = empty($_GET['iframe']) ? 0 : $_GET['iframe'];
$error = get_normal_msg("上传情况");
if (!User::logged()) {
    echo_error_body($loader,"请登录");
    redirect("/user/login.php?alert=请先登录&from=".get_full_cur_url_encoded());
    die;
}
if (!User::verified()) {
    redirect("/user/verify.php?from=".get_full_cur_url_encoded());
    die;
}
if (User::local_be_banned($loader->info->conn)) {
    notice_be_banned(User::be_banned_to());
    die;
}
$img_list = Imgur::get_list_by_uid($loader->info->conn, $_SESSION['uid']);
$total_memory = 0;
foreach ($img_list as $img) $total_memory += $img->size;
$out_of_memory = $total_memory > $config['max_imgur_memory'];
if ($step == 1) {
    $imgur = new Imgur($loader->info->conn);
    $imgur->type = $_FILES['image']['type'];
    $imgur->size = $_FILES['image']['size'];
    if (count($img_list) > $config['max_imgur_sum']) {
        $error = get_error_msg("你已经存放了 ".$config['max_imgur_sum']." 张图片，请先清理");
    } else if ($out_of_memory || $imgur->size + $total_memory > $config['max_imgur_memory']) {
        $error = get_error_msg("你的空间已满");
    } else if ($imgur->size > $config['max_imgur_single_memory'] || $imgur->size == 0) {
        $error = get_error_msg("文件太大或不存在！最大 ".round($config['max_imgur_single_memory'] / 1024 / 1024, 2)." MB");
    } else if (!Imgur::check_type($_FILES['image']['name'])) {
        $error = get_error_msg("暂不支持此类型文件");
    } else {
        $arr = explode('.', $_FILES['image']['name']);
        $sf = array_pop($arr);
        $imgur->content = file_get_contents($_FILES['image']['tmp_name']);
        $imgur->owner = $_SESSION['uid'];
        $imgur->suffix = $sf;
        $success = $imgur->upload();
        $error = $success ? get_success_msg("成功上传") : get_error_msg("上传失败");
    }
    die(json_encode(array(
            'msg' => $error,
            'fb' => array(
                    'src' => get_url_prefix().$config['domain'].$config['imgur_file_path'].$imgur->id.'_'.$imgur->md5.'.'.$imgur->suffix,
                    'time' => $imgur->upload_time,
                    'suffix' => $imgur->suffix,
                    'size' => $imgur->size,
                    'md5' => $imgur->md5,
                    'id' => $imgur->id
            )
    )));
}
$loader->init("上传图片");
$loader->add_css("/static/css/imgur.css");
$loader->init_end();
?>
<body>
<?php if (!$iframe) $loader->top_menu(); ?>
<div class="ui main container"  <?php if (!$iframe) { echo 'style="margin-top: 64px"'; } ?>>
    <div class="ui horizontal segments" style="align-items: center" id="file-upload">
        <div class="ui segment">
            <span>
                <label>
                    <div class="ui button">
                        <i class="file icon"></i>
                        选择文件
                        <input type="file" name="image" class="uploader" id="uploader" style="display: none" accept=".jpg,.png,.jpeg,.tiff,.webp,.gif,.bmp,.apng">
                    </div>
                </label>
                <label class="ui primary button" id="uploader-button">
                    <i class="cloud upload icon"></i>
                    开始上传
                    <input type="submit" onclick="upload()" <?php echo $out_of_memory || count($img_list) > $config['max_imgur_sum'] ? "disabled" : "" ?> style="display: none">
                </label>
            </span>
            <p style="float: right; margin-top: 8px;">
                （可拖拽上传）
            </p>
        </div>
        <div class="ui segment" id="msg">
            <?php echo $error; ?>
        </div>
    </div>
    <div class="ui segment" id="show-tmp-img-div" style="display: none">
        已选图片预览<br>
        <div id="path"></div>
        <img id="show-tmp-img" src="" alt="tmp" style="max-width: 160px">
        <div id="progress-txt"></div>
        <div class="ui bottom attached progress" id="progress">
            <div class="bar"></div>
        </div>
    </div>
    <div class="ui segment">
        你有 <?php echo count($img_list)." / ".$config['max_imgur_sum'] ?>  张图片，空间使用：
        <?php
        if ($out_of_memory) echo "<strong style=\"color:red\">";
        echo round($total_memory / 1024 / 1024, 2);
        echo ' MB / '.round($config['max_imgur_memory'] / 1024 / 1024, 2)." MB";
        if ($out_of_memory) echo "</strong>";
        ?>
        <table class="ui celled table">
            <thead>
            <tr>
                <th>图片</th>
                <th>链接</th>
                <th>时间</th>
                <th>大小</th>
                <th style="width: 120px">操作</th>
            </tr>
            </thead>
            <tbody id="imgs">
            <?php
            foreach ($img_list as $img) {
                $link = get_url_prefix().$config['domain'].$config['imgur_file_path'].$img->id."_".$img->md5.'.'.$img->suffix;
                ?>
                <tr id="img<?php echo $img->id ?>">
                    <td>
                        <img src="<?php echo $link ?>" alt="pic" style="max-width: 160px">
                    </td>
                    <td>
                        <span id="lnk-<?php echo $img->id ?>"><?php echo $link ?></span>
                        <i class="copy icon copier" data-clipboard-target="#lnk-<?php echo $img->id ?>" data-content="复制成功"></i>
                    </td>
                    <td><?php echo $img->upload_time ?></td>
                    <td><?php echo round($img->size / 1024, 2) ?> Kib</td>
                    <td>
                        <div class="ui button" onclick="delete_img('<?php echo $img->md5."','".$img->id ?>')">
                            <i class="trash alternate icon"></i>
                            删除
                        </div>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php if (!$iframe) $loader->footer(); ?>
</body>
<script>
    load_copier();
    <?php
    if ($iframe) {
    ?>
    let obj = parent.document.getElementById("imgur");
    function refresh_height() {
        setTimeout('obj.height = document.body.scrollHeight;', 100);
    }
    window.onload = function () {
        obj.height = document.body.scrollHeight;
    };
    window.onresize = function () {
        obj.height = document.body.scrollHeight;
    };
    <?php
    }
    ?>
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
        return ["jpg", "png", "jpeg", "tiff", "webp", "gif", "bmp", "apng"].includes(name.split('.').pop())
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
    function upload() {
        let file = file_input[0].files[0];
        if (!file) {
            alert("请选择文件！");
            return;
        }
        if (!check_file_type(file_input[0].files[0].name)) {
            alert("不支持的文件格式");
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
                    <i class="copy icon copier" data-clipboard-target="#lnk-' + obj['id'] + '" data-content="复制成功"></i>\
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
</script>
<?php
$loader->page_end();