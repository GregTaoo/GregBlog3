<?php
class RememberPw {
    public string $keypw, $password;
    public int $uid, $expires;
    public bool $exist = false;
    public mysqli $conn;

    public function __construct(?mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function set_exist(): self
    {
        $this->exist = true;
        return $this;
    }

    public static function add(?mysqli $conn, $email, $time): self
    {
        $rmber = new self($conn);
        $rmber->keypw = sha1(rand().$email).md5($time);
        $user = User::get_user_by_email($conn, $email, true);
        $uid = $user->uid;
        $password = $user->password;
        $rmber->expires = time() + $time;
        $rmber->uid = $uid;
        $rmber->password = $password;
        self::delete_expired($conn);
        $stmt = $conn->prepare("INSERT INTO rememberpw (uid, keypw, expires, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $uid, $rmber->keypw, $rmber->expires, $password);
        return $stmt->execute() ? $rmber->set_exist() : $rmber;
    }

    public static function delete_expired(?mysqli $conn): bool
    {
        $sql = "DELETE from rememberpw WHERE expires <= ".(time());
        return mysqli_query($conn, $sql);
    }

    public static function delete_by_key(?mysqli $conn, $keypw): bool
    {
        $stmt = $conn->prepare("DELETE FROM rememberpw WHERE keypw = ?");
        $stmt->bind_param("s", $keypw);
        return $stmt->execute();
    }

    public static function get_by_key(?mysqli $conn, $keypw): self
    {
        $stmt = $conn->prepare("SELECT * FROM rememberpw WHERE keypw = ?");
        $stmt->bind_param("s", $keypw);
        $rmber = new self($conn);
        $rmber->keypw = $keypw;
        if ($stmt->execute() && $arr = mysqli_fetch_array($stmt->get_result())) {
            $rmber->uid = $arr['uid'];
            $rmber->expires = $arr['expires'];
            $rmber->password = $arr['password'];
            $rmber->set_exist();
        }
        return $rmber;
    }

    public static function check(?mysqli $conn, $email, $keypw): bool
    {
        $rmber = self::get_by_key($conn, $keypw);
        if (!$rmber->exist || $rmber->expires <= time()) return false;
        $user = User::get_user_by_email($conn, $email, true);
        return $user->exist && $user->uid == $rmber->uid && $user->password == $rmber->password;
    }
}