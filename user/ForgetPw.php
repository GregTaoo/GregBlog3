<?php
class ForgetPw {
    public mysqli $conn;
    public int $id = -1, $uid = -1, $timestamp;
    public string $password, $code, $reason;
    public bool $checked = false, $exist = false;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public static function user_get_last_record(?mysqli $conn, $uid): self
    {
        $forgetpw = new self($conn);
        $forgetpw->uid = $uid;
        $forgetpw->query();
        return $forgetpw;
    }

    public static function create(?mysqli $conn, $uid, $password, $reason): self
    {
        $forgetpw = new self($conn);
        $forgetpw->uid = $uid;
        $forgetpw->reason = $reason;
        $forgetpw->password = password_hash($password, PASSWORD_DEFAULT);
        $forgetpw->insert();
        return $forgetpw;
    }

    public static function get_by_id(?mysqli $conn, $id): self
    {
        $forgetpw = new self($conn);
        $forgetpw->id = $id;
        $forgetpw->query();
        return $forgetpw;
    }

    public function insert(): bool
    {
        $this->code = uniqid().md5(rand().$this->uid);
        $this->timestamp = time();
        $stmt = $this->conn->prepare("INSERT INTO forgetpw (password, timestamp, code, uid, reason) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisis", $this->password, $this->timestamp, $this->code, $this->uid, $this->reason);
        if (!$stmt->execute()) return false;
        $res = mysqli_query($this->conn, "SELECT LAST_INSERT_ID() AS id FROM forgetpw");
        if (!$res) return false;
        $this->id = mysqli_fetch_array($res)['id'];
        $this->exist = true;
        return true;
    }

    public function query()
    {
        $stmt = $this->conn->prepare("SELECT * FROM forgetpw WHERE ".($this->uid == -1 ? "id = ?" : "uid = ?")." ORDER BY timestamp DESC LIMIT 1");
        echo mysqli_error($this->conn);
        $arg = $this->uid == -1 ? $this->id : $this->uid;
        $stmt->bind_param("i", $arg);
        $stmt->execute();
        if ($arr = mysqli_fetch_array($stmt->get_result())) {
            $this->uid = $arr['uid'];
            $this->id = $arr['id'];
            $this->password = $arr['password'];
            $this->timestamp = $arr['timestamp'];
            $this->code = $arr['code'];
            $this->checked = $arr['checked'];
            $this->reason = $arr['reason'];
            $this->exist = true;
        }
    }

    public function set_checked(): bool
    {
        $sql = "UPDATE forgetpw SET checked = 1 WHERE id = ".$this->id;
        return mysqli_query($this->conn, $sql);
    }

}