<?php
class Loader {
    public Info $info;
    public string $id;
    public int $msgs = 0, $start_microtime = 0;

    public function __construct($id)
    {
        $this->info = new Info();
        $this->id = $id;
        session_start();
    }

    public function init($title)
    {
        $this->start_microtime = microtime();
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
        return $this->id == $id ? "active" : "";
    }

    public function top_menu()
    {
        $config = Info::config();
        $from = $this->id != "login" ? get_full_cur_url_encoded() : "";
        $this->msgs = Message::get_user_messages($this->info->conn, User::uid());
        echo '
        <div class="ui fixed borderless icon menu top-menu">
            <div class="ui container">
                <a class="header item" href="/">
                    <img src="'.get_url_prefix().$config['domain'].$config['icon_path'].'" alt="logo">
                    '.$config['website_name'].'
                </a>
                <a class="item '.$this->is_active("search").'" href="/search" title="搜索">
                    <i class="search icon"></i>
                </a>
                <a class="item '.$this->is_active("post-blog").'" href="/blog/post.php" title="发帖">
                    <i class="edit icon"></i>
                </a>
                <a class="item '.$this->is_active("user-center").'" href="/user/center.php" title="">
                    <i class="user icon"></i>
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
        <div class="ui vertical footer">
            <div class="ui center aligned grid" id="footer">
                <div class="eight wide column">
                    <a href="https://github.com/gregtaoo/gregblog3">GregBlog</a>, by <a href="https://github.com/gregtaoo/">GregTao</a><br>
                    Proceeded in '.(microtime() - $this->start_microtime).' s
                </div>
                <div class="eight wide column">
                    <a href="/static/page/credits.html">Credits</a><br>
                    <a href="https://afdian.net/@gregtao">Donate</a>
                </div>
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
        self::add_js_extra($url, "");
    }

    public static function add_js_extra($url, $extra)
    {
        echo '<script src="'.$url.'" '.$extra.'></script>';
    }

    public static function get_postcards($config): array
    {
        return explode("|", $config['homepage_postcards']);
    }

    public static function get_postcard_links($config): array
    {
        return explode("|", $config['postcard_links']);
    }
}