<?php
include '../include.php';
session_start();
$config = Info::config();
if (!empty($_POST['type'])) {
    $type = $_POST['type'];
    switch ($type) {
        case "create": {
            if (!$config['allow_post_blog']) die("当前不允许发帖");
            if (!User::logged()) die("你尚未登录");
            if (!User::verified()) die("请先验证邮箱");
            $info = new Info();
            if (User::local_be_banned($info->conn)) die("你已被封禁");
            $editors = empty($_POST['editors']) ? "" : htmlspecialchars($_POST['editors']);
            $title = htmlspecialchars($_POST['title']);
            if (!is_text($title)) die("标题不得为空或过长（0-65535）");
            if (!check_length($_POST['text'], 100, 233333)) die("有点营养吧...主体长度限制（100-233333）");
            $text = $_POST['text'];
            $visible = $_POST['visible'] == "可见";
            if (!is_tinytext($_POST['tags'])) die("标签不得为空或过长（0-255）");
            $tags = htmlspecialchars($_POST['tags']);
            $intro = empty($_POST['intro']) ? $config['def_blog_desc'] : htmlspecialchars($_POST['intro']);
            $info = new Info();
            $blog = new Blog($info->conn, 0, false);
            $blog->editors = $editors;
            $blog->owner = new User($info->conn);
            $blog->owner->uid = $_SESSION['uid'];
            $blog->title = $title;
            $blog->visible = $visible;
            $blog->tags = $tags;
            $blog->intro = $intro;
            $blog->origin_text = $text;
            $blog->partition = "none";
            die($blog->create_blog() ? "success".$blog->id : "发帖失败");
        }
        case "edit": {
            if (!$config['allow_post_blog']) die("当前不允许发帖");
            if (!User::logged()) die("你尚未登录");
            if (!User::verified()) die("请先验证邮箱");
            $info = new Info();
            if (User::local_be_banned($info->conn)) die("你已被封禁");
            if (empty($_POST['id'])) die("找不到博客");
            $id = $_POST['id'];
            $info = new Info();
            $blog = new Blog($info->conn, $id, false);
            $blog->get_data();
            if (!$blog->have_permission()) die("你没有权限");
            if (!$blog->is_editor) $editors = empty($_POST['editors']) ? "" : htmlspecialchars($_POST['editors']);
            $title = htmlspecialchars($_POST['title']);
            if (!is_text($title)) die("标题不得为空或过长（0-65535）");
            if (!check_length($_POST['text'], 100, 233333)) die("有点营养吧...主体长度限制（100-233333）");
            $text = $_POST['text'];
            if (!$blog->is_editor) $visible = $_POST['visible'] == "可见";
            if (!is_tinytext($_POST['tags'])) die("标签不得为空或过长（0-255）");
            $tags = htmlspecialchars($_POST['tags']);
            $intro = empty($_POST['intro']) ? $config['def_blog_desc'] : htmlspecialchars($_POST['intro']);
            if (!$blog->is_editor) $blog->editors = $editors;
            $blog->title = $title;
            if (!$blog->is_editor) $blog->visible = $visible;
            $blog->tags = $tags;
            $blog->intro = $intro;
            $blog->origin_text = $text;
            $blog->partition = "none";
            die($blog->update() ? "success".$blog->id : "编辑失败");
        }
        case "delete": {
            if (!User::logged()) die("你尚未登录");
            if (!User::verified()) die("请先验证邮箱");
            if (empty($_POST['id'])) die("找不到博客");
            $id = $_POST['id'];
            $info = new Info();
            $blog = new Blog($info->conn, $id, false);
            $blog->get_data();
            if ($blog->owner->uid != $_SESSION['uid'] && !$_SESSION['admin']) die("你没有权限");
            die($blog->delete() ? "success" : "删除失败");
        }
        case "delete-reply": {
            if (!User::logged()) die("你尚未登录");
            if (!User::verified()) die("请先验证邮箱");
            $sub = !empty($_POST['sub']) && $_POST['sub'] == "true";
            $in_blog = $_POST['in_blog'];
            $floor = $_POST['floor'];
            $sub_floor = $_POST['sub_floor'];
            $info = new Info();
            $reply = new Reply($info->conn);
            $reply->sub = $sub;
            $reply->in_blog = $in_blog;
            $reply->floor = $floor;
            $reply->sub_floor = $sub_floor;
            $reply->query();
            if (!User::admin() && $reply->owner != User::uid()) die("你没有权限");
            die($reply->delete() ? "success" : "删除失败");
        }
        case "post-reply": {
            if (!$config['allow_reply']) die("当前不允许评论");
            if (!User::logged()) die("你尚未登录");
            if (!User::verified()) die("请先验证邮箱");
            $info = new Info();
            if (User::local_be_banned($info->conn)) die("你已被封禁");
            $sub = !empty($_POST['sub']) && $_POST['sub'] == "true";
            $in_blog = $_POST['in_blog'];
            if (!check_length($_POST['text'], 0, 2333)) die("长度限制（0-2333）");
            $text = htmlspecialchars($_POST['text']);
            $floor = $_POST['floor'];
            $owner = User::uid();
            $info = new Info();
            $reply = new Reply($info->conn);
            $reply->in_blog = $in_blog;
            $reply->floor = $floor;
            $reply->sub = $sub;
            $reply->text = $text;
            $reply->owner = $owner;
            $statu = $reply->insert();
            $arr = array(
                'statu' => ($statu ? "success" : "失败"),
                'reply' => $statu ? $reply->to_json_array() : array()
            );
            Message::add_reply_message($info->conn, $owner, $sub, $in_blog, $text, $reply->floor, $reply->reply_id);
            die(json_encode($arr));
        }
        case "get-reply": {
            $page = empty($_POST['page']) ? 0 : $_POST['page'];
            if (empty($_POST['in_blog'])) die("博客不存在");
            $in_blog = $_POST['in_blog'];
            $info = new Info();
            $json_arr = Reply::get_json_from_blog($info->conn, $in_blog, $page);
            die(json_encode($json_arr));
        }
        case "get-sub-reply": {
            $page = empty($_POST['page']) ? 0 : $_POST['page'];
            if (empty($_POST['in_blog'])) die("博客不存在");
            $in_blog = $_POST['in_blog'];
            $floor = $_POST['floor'];
            $info = new Info();
            $json_arr = Reply::get_sub_json_from_blog($info->conn, $in_blog, $floor, $page);
            die(json_encode($json_arr));
        }
        case "randomly-select-blogs": {
            $amount = empty($_POST['amount']) ? 0 : $_POST['amount'];
            if (!is_numeric($amount) || !in_range($amount, 1, 20)) die;
            $info = new Info();
            die(json_encode(Blog::randomly_select_as_json_list($info, $amount)));
        }
        case "select-blogs-by-rank": {
            $amount = empty($_POST['amount']) ? 0 : $_POST['amount'];
            if (!is_numeric($amount) || !in_range($amount, 1, 20)) die;
            $info = new Info();
            die(json_encode(Blog::select_by_rank_as_json($info->conn, $amount)));
        }
        case "get-broadcasts-list": {
            $info = new Info();
            die(json_encode(Broadcast::get_broadcasts_json($info->conn, 0, 5)));
        }
        case "get-emotions-array": {
            $array = Info::emotions();
            foreach ($array as $key=>&$lnk) {
                $http = substr($lnk, 0, 7);
                $lnk = $http == "http://" || $http == "https:/" ? $lnk : ($config['use_local_emotions'] == 1 ? "/static/cdn/img/" : "https://unpkg.com/gregblog-cdn/img/").$lnk;
            }
        }
        default: {
            die("未知的操作");
        }
    }
} else die("未知的操作");