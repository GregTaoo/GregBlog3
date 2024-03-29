<?php
include '../include.php';
$loader = new Loader("admin-api");
if (!User::logged() || !User::verified() || !User::admin()) die("Permission Error");
$manage = empty($_POST['manage']) ? "" : $_POST['manage'];
switch ($manage) {
    case "give-title": {
        if (empty($_POST['uid'])) die("!请填写用户uid");
        $uid = $_POST['uid'];
        if (!is_numeric($uid)) die("!请填写用户uid");
        $title = empty($_POST['title']) ? "" : $_POST['title'];
        $user = User::get_user_by_uid($loader->info->conn, $uid, true);
        $user->title = $title;
        die($user->update_simply() ? "成功授予了用户<a href=\"../user/space.php?uid=".$uid."\">".$user->nickname."</a> (UID: ".$uid.") 头衔：".$title : "!授予失败");
    }
    case "ban-user": {
        if (empty($_POST['uid'])) die("!请填写用户uid");
        $uid = $_POST['uid'];
        if (!is_numeric($uid)) die("!请填写用户uid");
        if (empty($_POST['time'])) die("!请填写时长");
        $time = $_POST['time'];
        if (!is_numeric($time)) die("!请填写时长");
        $time += time();
        $user = User::get_user_by_uid($loader->info->conn, $uid, true);
        $user->ban = $time;
        die($user->update_simply() && Message::add_be_banned_message($loader->info->conn, $uid, $time) ?
            "成功封禁用户<a href=\"../user/space.php?uid=".$uid."\">".$user->nickname."</a> (UID: ".$uid.")到".date("Y年m月d日H时i分s秒", $time) : "!失败");
    }
    case "get-imgur": {
        $page = empty($_POST['page']) ? 0 : $_POST['page'];
        $uid = empty($_POST['uid']) ? 0 : $_POST['uid'];
        if (!is_numeric($uid)) die("!请输入正确的uid");
        die(json_encode(Imgur::get_json_by_page($loader->info->conn, $page, $uid)));
    }
    case "get-broadcasts-list": {
        $page = empty($_POST['page']) ? 0 : $_POST['page'];
        die(json_encode(Broadcast::get_broadcasts_json($loader->info->conn, $page, 20)));
    }
    case "update-broadcast": {
        $id = empty($_POST['id']) ? 0 : $_POST['id'];
        $edit = $_POST['edit'] == "true";
        if ($edit && $id == 0) die("未指定id");
        $title = $_POST['title'];
        $link = $_POST['link'];
        $type = $_POST['type'];
        $stick = $_POST['stick'] == "true";
        if ($edit) {
            $bc = Broadcast::from_id($loader->info->conn, $id);
            $bc->title = $title;
            $bc->link = $link;
            $bc->type = $type;
            $bc->stick = $stick;
            die($bc->update($loader->info->conn) ? json_encode($bc) : "failed");
        } else {
            die(Broadcast::add_broadcast($loader->info->conn, $link, $type, $stick, $title, $bc) ? json_encode($bc) : "failed");
        }
    }
    case "delete-broadcast": {
        $id = empty($_POST['id']) ? 0 : $_POST['id'];
        if ($id == 0) die("id不存在");
        die(Broadcast::delete_broadcast($loader->info->conn, $id) ? "success" : "删除失败");
    }
    case "get-config-array": {
        die(json_encode(Info::config()));
    }
    case "update-config-array": {
        if (empty($_POST['json'])) die("字符串为空");
        $json_str = $_POST['json'];
        $json = json_decode($json_str);
        if (json_last_error() != JSON_ERROR_NONE) die("json格式错误：".json_last_error_msg());
        $file = fopen($_SERVER['DOCUMENT_ROOT']."/../config/Config.json", "w") or die("文件打开失败");
        echo fwrite($file, $json_str) ? 'success' : 'failed';
        fclose($file);
        die;
    }
    case "get-emotions-array": {
        die(json_encode(Info::emotions()));
    }
    case "update-emotions-array": {
        if (empty($_POST['json'])) die("字符串为空");
        $json_str = $_POST['json'];
        $json = json_decode($json_str);
        if (json_last_error() != JSON_ERROR_NONE) die("json格式错误：".json_last_error_msg());
        $file = fopen($_SERVER['DOCUMENT_ROOT']."/../config/Emotions.json", "w") or die("文件打开失败");
        echo fwrite($file, $json_str) ? 'success' : 'failed';
        fclose($file);
        die;
    }
    case "get-blog-list": {
        $page = empty($_POST['page']) ? 0 : $_POST['page'];
        die(json_encode(Blog::get_admin_blog_json_list($loader->info->conn, $page)));
    }
    case "get-config-backup": {
        die(json_encode((array) file_get_contents($_SERVER['DOCUMENT_ROOT']."/../server/ConfigBackup.json")));
    }
    case "get-emotions-backup": {
        die(json_encode((array) file_get_contents($_SERVER['DOCUMENT_ROOT']."/../server/EmotionsBackup.json")));
    }
    case "email-test": {
        $email = $_POST['email'];
        $title = $_POST['title'];
        $body = $_POST['body'];
        if (empty($email)) die("error empty");
        die(Mail::send_email($email, $title, $body, $err) ? "success" : "error ".$err);
    }
    default: {
        die;
    }
}