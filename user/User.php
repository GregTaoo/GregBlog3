<?php
class User {
    public int $uid = 0, $verify_time, $admin, $ban = 0;
    public string $nickname, $email, $password, $regtime, $verify_code, $intro, $title = "";
    public bool $verified, $exist = false, $allow_be_srch = true;

    public mysqli $conn;
    public bool $personal = false;

    private static function get_basic_sql($personal): string
    {
        return $personal ? "SELECT * FROM users " : "SELECT nickname, uid, email, regtime, admin, intro, ban, title, allow_be_srch FROM users ";
    }

    private static array $sqls = array(array(), array());

    public function __construct($conn)
    {
        $this->conn = $conn;
        self::$sqls[0]['uid'] = self::get_basic_sql(false)."WHERE uid = ?";
        self::$sqls[1]['uid'] = self::get_basic_sql(true)."WHERE uid = ?";
        self::$sqls[0]['email'] = self::get_basic_sql(false)."WHERE email = ?";
        self::$sqls[1]['email'] = self::get_basic_sql(true)."WHERE email = ?";
        self::$sqls[0]['nickname'] = self::get_basic_sql(false)."WHERE nickname = ?";
        self::$sqls[1]['nickname'] = self::get_basic_sql(true)."WHERE nickname = ?";
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
    
    public function fetch_data_from_arr($arr, $type)
    {
        $this->exist = true;
        $this->nickname = $arr['nickname'];
        if ($type != "uid") $this->uid = $arr['uid'];
        else if ($type != "email") $this->email = $arr['email'];
        else if ($type != "nickname") $this->nickname = $arr['nickname'];
        $this->regtime = $arr['regtime'];
        $this->admin = $arr['admin'];
        $this->intro = $arr['intro'];
        $this->ban = $arr['ban'];
        $this->title = $arr['title'];
        $this->allow_be_srch = $arr['allow_be_srch'];
        if ($this->personal) {
            $this->verify_time = $arr['verify_time'];
            $this->verified = $arr['verified'];
            $this->password = $arr['password'];
            $this->verify_code = $arr['verify_code'];
        }
    }

    public function query($type)
    {
        try {
            if (empty(self::$sqls[0][$type])) throw new Exception("No such type: " . $type);
        } catch (Exception $e) {
            die($e);
        }
        $sql = self::$sqls[$this->personal][$type];
        $stmt = $this->conn->prepare($sql);
        if ($type == "email") $stmt->bind_param("s", $this->email);
        else if ($type == "uid") $stmt->bind_param("i", $this->uid);
        else if ($type == "nickname") $stmt->bind_param("s", $this->nickname);
        $stmt->execute();
        if ($arr = mysqli_fetch_array($stmt->get_result())) {
            $this->fetch_data_from_arr($arr, $type);
        }
    }

    public function update_simply(): bool
    {
        $stmt = $this->conn->prepare("UPDATE users SET nickname = ?, password = ?, intro = ?, title = ?, allow_be_srch = ?, ban = ? WHERE uid = ?");
        $stmt->bind_param("ssssiii", $this->nickname,$this->password, $this->intro, $this->title, $this->allow_be_srch, $this->ban, $this->uid);
        return $stmt->execute();
    }

    public static function try_login_with_cookie(?mysqli $conn): bool
    {
        if (!empty($_COOKIE['email']) && !empty($_COOKIE['keypw'])) {
            $email = $_COOKIE['email'];
            $keypw = $_COOKIE['keypw'];
            if (RememberPw::check($conn, $email, $keypw)) {
                $user = User::get_user_by_email($conn, $email, true);
                $user->set_session();
                return true;
            }
        }
        return false;
    }

    public static function set_auto_login_cookie(?mysqli $conn, $email)
    {
        $rmber = RememberPw::add($conn, $email, 7 * 24 * 60 * 60);
        setcookie("email", $email, $rmber->expires, '/');
        setcookie("keypw", $rmber->keypw, $rmber->expires, '/');
    }

    public static function clear_auto_login_cookie(?mysqli $conn)
    {
        if (!empty($_COOKIE['keypw'])) RememberPw::delete_by_key($conn, $_COOKIE['keypw']);
        setcookie("email", "", 0, '/');
        setcookie("keypw", "", 0, '/');
    }

    public static function login($conn, $email, $password, &$error): bool
    {
        $user = User::get_user_by_email($conn, $email, true);
        if ($user->exist && (password_verify($password, $user->password) || md5($password) == $user->password)) {
            $user->set_session();
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
        if ($this->exist) return false;
        $config = Info::config();
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->regtime = (new DateTime())->format('Y-m-d H:i:s');
        $stmt = $this->conn->prepare("INSERT INTO users (nickname, email, regtime, password, intro, title) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $this->nickname, $this->email, $this->regtime, $this->password, $config['def_user_desc'], $this->title);
        if (!$stmt->execute()) return false;
        $this->uid = mysqli_fetch_array(mysqli_query($this->conn, "SELECT LAST_INSERT_ID() AS uid FROM users"))['uid'];
        Message::add_reg_message_to_admins($this->conn, $this);
        self::create_collection($this->conn, $this->uid);
        return true;
    }

    public static function get_user_by_uid($conn, $uid, $personal): User
    {
        $user = new User($conn);
        $user->uid = $uid;
        $user->personal = $personal;
        $user->query("uid");
        return $user;
    }

    public static function get_user_by_email($conn, $email, $personal): User
    {
        $user = new User($conn);
        $user->email = $email;
        $user->personal = $personal;
        $user->query("email");
        return $user;
    }

    public function set_session()
    {
        $_SESSION['nickname'] = $this->nickname;
        $_SESSION['admin'] = $this->admin;
        $_SESSION['uid'] = $this->uid;
        $_SESSION['email'] = $this->email;
        $_SESSION['loggedin'] = true;
        $_SESSION['verified'] = $this->verified;
        $_SESSION['ban'] = $this->ban;
    }

    public function send_verify_code(): bool
    {
        if (!$this->exist || $this->verified) return false;
        $config = Info::config();
        $stmt = $this->conn->prepare("UPDATE users SET verify_code = ?, verify_time = ? WHERE uid = ?");
        $code = rand(100000, 999999);
        $time = time();
        $stmt->bind_param("iii", $code, $time, $this->uid);
        $body = "<h3>这里是 ".$config['website_name']."</h3><br>请点击链接验证您的邮箱：
                <a href=\"".get_url_prefix().$config['domain']."/user/verify.php?code=".$code."\">链接至验证页面</a><br>";
        return $stmt->execute() && Mail::send_email($this->email, "验证您的 ".$config['website_name']." 账户邮箱", $body, $err);
    }

    public function set_to_verified(): bool
    {
        if (!$this->exist || $this->verified) return false;
        $sql = "UPDATE users SET verified = 1 WHERE uid = ".$this->uid;
        return mysqli_query($this->conn, $sql);
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
        $address = strtolower(trim($this->email));
        $hash = md5($address);
        return 'https://cravatar.cn/avatar/'.$hash.'?d='.$config['def_user_avatar'];
    }

    function get_avatar_img(): string
    {
        return '<img src="'.$this->get_avatar().'" style="border-radius: 50%; height: 64px; width: 64px;">';
    }

    public static function uid(): int
    {
        return empty($_SESSION['uid']) ? 0 : $_SESSION['uid'];
    }

    public static function nickname(): string
    {
        return empty($_SESSION['nickname']) ? 'NULL' : $_SESSION['nickname'];
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

    public static function update_ban($conn)
    {
        $user = self::get_user_by_uid($conn, User::uid(), true);
        $user->set_session();
    }

    public static function local_be_banned($conn): bool
    {
        self::update_ban($conn);
        return !empty($_SESSION['ban']) && $_SESSION['ban'] >= time();
    }

    public function be_banned(): bool
    {
        return $this->ban != 0 && $this->ban >= time();
    }

    public static function be_banned_to(): int
    {
        return empty($_SESSION['ban']) ? 0 : $_SESSION['ban'];
    }
}