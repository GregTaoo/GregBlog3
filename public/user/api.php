<?php
include '../include.php';
$loader = new Loader("user-api");
if (!empty($_GET['logout']) && $_GET['logout'] == 1) {
    session_destroy();
    User::clear_auto_login_cookie($loader->info->conn);
    $logout = cur_url_decode($_GET['from']);
    $config = Info::config();
    $logout = empty($logout) ? get_url_prefix().$config['domain'] : $logout;
    redirect($logout);
    die;
}
if (empty($_POST['type'])) die;
$type = $_POST['type'];
$config = Info::config();
switch ($type) {
    case "register": {
        if (!$config['allow_register']) die("当前不允许注册");
        $email = $_POST['email'];
        $password = $_POST['password'];
        $nickname = $_POST['nickname'];
        if (!is_tinytext($email) || empty($email)) die("邮箱长度限制为1~255字符，你的邮箱长度为".strlen($email));
        if (!preg_match("/[\w\-_]+@[\w\-_]+.\w+/", $email)) die("邮箱格式错误");
        if (!check_length($password, 6, 100)) die("请填写密码（长度6~100）");
        if (!check_length($nickname, 1, 40)) die("昵称长度限制为1~40字符，你的昵称长度为".strlen($nickname));
        if (!preg_match("/[a-zA-Z-_\u4e00-\u9fa5]+/", $nickname)) die("昵称仅允许英文字母以及减号、下划线、中文字符");
        $user = new User($loader->info->conn);
        $user->email = $email;
        $user->query("email");
        if ($user->exist) die("该邮箱已经被注册");
        $user = new User($loader->info->conn);
        $user->nickname = $nickname;
        $user->query("nickname");
        if ($user->exist) die("该用户名已经被注册");
        $user->email = $email;
        $user->password = $password;
        $user->nickname = $nickname;
        die($user->register() ? "success" : "注册失败");
    }
    case "login": {
        if (!$config['allow_login']) die("当前不允许登录");
        $email = $_POST['email'];
        $password = $_POST['password'];
        $auto_login = $_POST['auto-login'];
        if (!is_tinytext($email) || empty($email)) die("邮箱长度限制为1~255字符，你的邮箱长度为".strlen($email));
        if (!check_length($password, 6, 100)) die("请填写6-100位密码");
        $success = User::login($loader->info->conn, $email, $password, $error);
        if ($success && $auto_login == "true") User::set_auto_login_cookie($loader->info->conn, $email);
        die($success ? "success" : "登录失败，".$error);
    }
    case "manage": {
        if (!User::logged() || empty($_SESSION['uid'])) die("你尚未登录");
        $user = User::get_user_by_uid($loader->info->conn, User::uid(), true);
        $nickname = $_POST['nickname'];
        if (!check_length($nickname, 1, 40)) die("昵称长度限制为1~40字符，你的昵称长度为".strlen($nickname));
        $intro = empty($_POST['intro']) ? $config['def_user_desc'] : htmlspecialchars($_POST['intro']);
        if (!is_text($intro)) die("个性签名长度限制为0~65535个字符，你的签名长度为".strlen($intro));
        $user->nickname = $nickname;
        if ($nickname != User::nickname()) {
            $usercheck = new User($loader->info->conn);
            $usercheck->nickname = $nickname;
            $usercheck->query("nickname");
            if ($usercheck->exist) die("该用户名已经被占用");
        }
        $user->intro = $intro;
        $password = $_POST['password'];
        $user->allow_be_srch = empty($_POST['allow_be_srch']) || $_POST['allow_be_srch'] == "true";
        if (!empty($password)) {
            if (!check_length($password, 6, 100)) die("请填写6-100位密码");
            $user->password = password_hash($password, PASSWORD_DEFAULT);
            session_destroy();
        }
        if ($user->update_simply()) {
            $user->set_session();
            die("success");
        }
        die("修改失败");
    }
    case "get-message": {
        if (!User::logged() || empty($_SESSION['uid'])) die("你尚未登录");
        $uid = User::uid();
        $page = empty($_POST['page']) ? 0 : $_POST['page'];
        die(json_encode(Message::get_from_user($loader->info->conn, $uid, $page)));
    }
    case "get-blog-list": {
        $uid = empty($_POST['uid']) ? 0 : $_POST['uid'];
        $page = empty($_POST['page']) ? 0 : $_POST['page'];
        $self = User::uid() == $uid;
        die(json_encode(Blog::get_blog_json_list($loader->info->conn, $uid, $page, $self)));
    }
    case "add-collect": {
        if (!User::logged() || empty($_SESSION['uid'])) die("你尚未登录");
        $blogid = empty($_POST['blogid']) ? 0 : $_POST['blogid'];
        if (!is_numeric($blogid)) die("不是数字");
        $blog = new Blog($loader->info->conn, $blogid, false);
        $blog->get_data();
        if (!$blog->exist) die("博客不存在");
        $clt = new Collection($loader->info->conn, User::uid());
        if ($clt->cnt >= Info::config()['collection_size']) die("收藏夹已满");
        die($clt->add_collect($blog, $err) ? 'success' : '收藏失败，'.$err);
    }
    case "dis-collect": {
        if (!User::logged() || empty($_SESSION['uid'])) die("你尚未登录");
        $blogid = empty($_POST['blogid']) ? 0 : $_POST['blogid'];
        if (!is_numeric($blogid)) die("不是数字");
        $blog = new Blog($loader->info->conn, $blogid, false);
        $blog->get_data();
        if (!$blog->exist) die("博客不存在");
        $clt = new Collection($loader->info->conn, User::uid());
        die($clt->dis_collect($blog, $err) ? 'success' : '取消收藏失败，'.$err);
    }
    case "get-collections": {
        if (!User::logged() || empty($_SESSION['uid'])) die("你尚未登录");
        $page = empty($_POST['page']) ? 0 : $_POST['page'];
        die(json_encode(Collection::get_by_user($loader->info->conn, User::uid(), $page)));
    }
    default: {
        die("未知错误");
    }
}