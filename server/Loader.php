<?php
class Loader {
    public Info $info;
    public string $id;
    public int $msgs = 0;

    public function __construct($id)
    {
        $this -> info = new Info();
        $this -> id = $id;
        session_start();
    }

    public function init($title)
    {
        $config = Info::config();
        if (!User::logged()) {
            User::try_login_with_cookie($this->info->conn);
        }
        $title = $title." - ".$config['website_name'];
        echo <<<LABEL
        <!DOCTYPE html>
        <html lang="zh_cn">
            <head>
                <title>$title</title>
                <meta name="viewport" content="width=device-width, initial-scale=0.85">
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
LABEL;
        echo '<link rel="shortcut icon" href="'.$config['icon_ico_path'].'">';
        echo '<link rel="icon" href="'.$config['icon_ico_path'].'">';
        self::add_js($config['jquery_js_src']);
        self::add_css($config['semantic_ui_css_src']);
        self::add_js($config['semantic_ui_js_src']);
        self::add_js("/static/js/func.js");
        self::add_css("/static/css/all.css");
        self::add_js($config['clipboard_js_src']);
    }

    public function init_end()
    {
        echo <<<LABEL
            </head>
LABEL;
    }

    public function page_end()
    {
        echo <<<LABEL
        </html>
LABEL;

    }

    public function is_active($id): string
    {
        return $this -> id == $id ? "active" : "";
    }

    public function top_menu()
    {
        $config = Info::config();
        $from = $this -> id != "login" ? get_full_cur_url_encoded() : "";
        $this->msgs = Message::get_user_messages($this->info->conn, User::uid());
        echo '
        <div class="ui fixed borderless menu top-menu">
            <div class="ui container">
                <a class="item '.$this -> is_active("index").'" href="/">
                    <img src="http://'.Info::$domain.$config['icon_path'].'">
                </a>
                <a class="item '.$this -> is_active("search").'" href="/search">
                    <i class="search icon"></i>
                    搜索
                </a>
                <a class="item '.$this -> is_active("post-blog").'" href="/blog/post.php">
                    <i class="edit icon"></i>
                    发帖
                </a>
                <a class="item '.$this -> is_active("user-center").'" href="/user/center.php">
                    <i class="user icon"></i>
                    个人
                     '.($this->msgs > 0 ? '<div class="ui mini red label">'.$this->msgs.'</div>' : '').'
                </a>
                <div class="right menu">
                    <div class="item">
                        <a class="ui green basic button" href="'.(User::logged() ? '/user/api.php?logout=1&from='.$from : '/user/login.php?from='.$from).'">
                            '.(User::logged() ? "登出" : "登录").'
                        </a>
                    </div>
                </div>
            </div>
        </div>
        ';
    }

    public function footer()
    {
        echo '
        <div class="ui vertical footer segment" style="color: #9b9b9b">
            <div class="ui center aligned container">
                GregBlog, Managed by <a href="/user/space.php?uid=1">GregTao</a><br>
                <a href="/static/page/credits.html">Credits</a>
            </div>
        </div>
        ';
    }

    public static function add_css($url)
    {
        echo '<link rel="stylesheet" type="text/css" href="'.$url.'">';
    }

    public static function add_js($url)
    {
        echo '<script src="'.$url.'"></script>';
    }
}