<?php
class User {
    public int $uid, $verify_time, $admin, $ban = 0;
    public string $nickname, $email, $password, $regtime, $verify_code, $intro, $title = "";
    public bool $verified, $exist = false, $allow_be_srch = true;

    public mysqli $conn;
    public bool $personal = false;
    public static string $sql = "SELECT nickname, email, regtime, admin, intro, ban, title, allow_be_srch FROM users WHERE uid = ?";
    public static string $sql_personal = "SELECT * FROM users WHERE uid = ?";
    public static string $sql_email = "SELECT nickname, uid, regtime, admin, intro, ban, title, allow_be_srch FROM users WHERE email = ?";
    public static string $sql_email_personal = "SELECT * FROM users WHERE email = ?";

    public function __construct($conn)
    {
        $this -> conn = $conn;
    }

    public function get_collection(): Collection
    {
        return new Collection($this->conn, $this->uid);
    }

    public function get_json_array(): array
    {
        return array(
            'nickname' => $this->nickname,
            'intro' => $this->intro,
            'uid' => $this->uid,
            'admin' => $this->admin,
            'title' => $this->title,
            'email' => $this->email,
            'emmd5' => md5($this->email)
        );
    }

    public function query($is_email)
    {
        $sql = $is_email ? ($this -> personal ? User::$sql_email_personal : User::$sql_email)
            : ($this -> personal ? User::$sql_personal : User::$sql);
        $stmt = $this -> conn -> prepare($sql);
        if ($is_email) $stmt -> bind_param("s", $this -> email);
        else $stmt -> bind_param("i", $this -> uid);
        $stmt -> execute();
        if ($arr = mysqli_fetch_array($stmt -> get_result())) {
            $this -> exist = true;
            $this -> nickname = $arr['nickname'];
            if ($is_email) $this -> uid = $arr['uid'];
            else $this -> email = $arr['email'];
            $this -> regtime = $arr['regtime'];
            $this -> admin = $arr['admin'];
            $this -> intro = $arr['intro'];
            $this->ban = $arr['ban'];
            $this->title = $arr['title'];
            $this->allow_be_srch = $arr['allow_be_srch'];
            if ($this -> personal) {
                $this -> verify_time = $arr['verify_time'];
                $this -> verified = $arr['verified'];
                $this -> password = $arr['password'];
                $this -> verify_code = $arr['verify_code'];
            }
        }
    }

    public function update_simply(): bool
    {
        $stmt = $this -> conn -> prepare("UPDATE users SET nickname = ?, password = ?, intro = ?, title = ?, allow_be_srch = ? WHERE uid = ?");
        $stmt -> bind_param("ssssii", $this -> nickname, $this -> password, $this -> intro, $this->title, $this->allow_be_srch, $this -> uid);
        return $stmt -> execute();
    }

    public static function try_login_with_cookie($conn): bool
    {
        if (!empty($_COOKIE['email']) && !empty($_COOKIE['password'])) {
            return self::login($conn, $_COOKIE['email'], $_COOKIE['password'], $err);
        }
        return false;
    }

    public static function set_auto_login_cookie($email, $password)
    {
        setcookie("email", $email, time() + 7 * 24 * 60 * 60, '/');
        setcookie("password", $password, time() + 7 * 24 * 60 * 60, '/');
    }

    public static function clear_auto_login_cookie()
    {
        setcookie("email", "", 0, '/');
        setcookie("password", "", 0, '/');
    }

    public static function login($conn, $email, $password, &$error): bool
    {
        $user = User::get_user_email($conn, $email, true);
        if (time() < $user->ban) {
            $error = "你已经被封禁到".date("Y-m-d H:i:s", $user->ban);
        }
        if ($user -> exist && (password_verify($password, $user->password) || md5($password) == $user->password)) {
            $user -> set_session();
            return true;
        }
        $error = "用户名或密码错误";
        return false;
    }

    public static function create_collection($conn, $uid): bool
    {
        $sql = "INSERT INTO collections (uid, json) VALUES (".$uid.", JSON_OBJECT())";
        return mysqli_query($conn, $sql);
    }

    public function register(): bool
    {
        if ($this -> exist) return false;
        $config = Info::config();
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this -> regtime = (new DateTime()) -> format('Y-m-d H:i:s');
        $stmt = $this -> conn -> prepare("INSERT INTO users (nickname, email, regtime, password, intro, title) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt -> bind_param("ssssss", $this -> nickname, $this -> email, $this -> regtime, $this -> password, $config['def_user_desc'], $this->title);
        if (!$stmt -> execute()) return false;
        $this->uid = mysqli_fetch_array(mysqli_query($this->conn, "SELECT LAST_INSERT_ID() AS uid FROM users"))['uid'];
        Message::add_reg_message_to_admins($this->conn, $this);
        self::create_collection($this->conn, $this->uid);
        return true;
    }

    public static function get_user($conn, $uid, $personal): User
    {
        $user = new User($conn);
        $user -> uid = $uid;
        $user -> personal = $personal;
        $user -> query(false);
        return $user;
    }

    public static function get_user_email($conn, $email, $personal): User
    {
        $user = new User($conn);
        $user -> email = $email;
        $user -> personal = $personal;
        $user -> query(true);
        return $user;
    }

    public function set_session()
    {
        $_SESSION['nickname'] = $this -> nickname;
        $_SESSION['admin'] = $this -> admin;
        $_SESSION['uid'] = $this -> uid;
        $_SESSION['email'] = $this -> email;
        $_SESSION['loggedin'] = true;
        $_SESSION['verified'] = $this -> verified;
    }

    public function send_verify_code(): bool
    {
        if (!$this -> exist || $this -> verified) return false;
        $config = Info::config();
        $stmt = $this -> conn -> prepare("UPDATE users SET verify_code = ?, verify_time = ? WHERE uid = ?");
        $code = rand(100000, 999999);
        $time = time();
        $stmt -> bind_param("iii", $code, $time, $this -> uid);
        $body = "<h3>这里是".$config['website_name']."</h3><br>请点击链接验证您的邮箱：
                <a href=\"http://".Info::$domain."/user/verify.php?code=".$code."\">链接至验证页面</a>";
        return $stmt -> execute() && Mail::send_email($this -> email, "验证您的邮箱", $body);
    }

    public function set_to_verified(): bool
    {
        if (!$this -> exist || $this -> verified) return false;
        $sql = "UPDATE users SET verified = 1 WHERE uid = ".$this -> uid;
        return mysqli_query($this -> conn, $sql);
    }

    public static function logged(): bool
    {
        return !empty($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1;
    }

    public static function verified(): bool
    {
        return !empty($_SESSION['verified']) && $_SESSION['verified'] == 1;
    }

    function get_avatar(): string
    {
        $config = Info::config();
        $address = strtolower(trim($this -> email));
        $hash = md5($address);
        return 'https://cravatar.cn/avatar/'.$hash.'?d='.$config['def_user_avatar'];
    }

    function get_avatar_img(): string
    {
        return '<img src="'.$this -> get_avatar().'" style="border-radius: 50%; height: 64px; width: 64px;">';
    }

    public static function uid(): int
    {
        return empty($_SESSION['uid']) ? 0 : $_SESSION['uid'];
    }

    public static function admin(): int
    {
        return empty($_SESSION['admin']) ? 0 : $_SESSION['admin'];
    }

    public static function set_view_blog($id)
    {
        $cookie = empty($_COOKIE["view_b"]) ? "" : $_COOKIE["view_b"];
        $arr = unserialize($cookie);
        $arr[$id] = 1;
        setcookie("view_b", serialize($arr), 2147483647, '/');
    }

    public static function viewed_blog($id): bool
    {
        $cookie = empty($_COOKIE["view_b"]) ? "" : $_COOKIE["view_b"];
        $arr = unserialize($cookie);
        return isset($arr[$id]);
    }

    public static function get_admin_label(): string
    {
        return '<div class="ui mini teal horizontal label">管理员</div>';
    }

    public static function get_owner_label(): string
    {
        return '<div class="ui mini orange horizontal label">作者</div>';
    }

    public static function get_title_label($conn, $uid): string
    {
        if (!$uid instanceof User) {
            $sql = "SELECT title FROM users WHERE uid = ".$uid;
            $title = mysqli_fetch_array(mysqli_query($conn, $sql))['title'];
        } else $title = $uid->title;
        if (empty($title)) return "";
        return '<div class="ui mini blue horizontal label">'.$title.'</div>';
    }

    public static function get_title_label_directly($str): string
    {
        if (empty($str)) return '';
        return '<div class="ui mini blue horizontal label">'.$str.'</div>';
    }
}