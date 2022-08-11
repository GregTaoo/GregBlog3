<?php
class Message {
    public int $from, $to, $id;
    public string $time, $text, $type;

    public mysqli $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function to_json_array(): array
    {
        $user = new User($this->conn);
        $user->uid = $this->from;
        $user->query(false);
        return array(
            'from' => $this->from,
            'from_nickname' => $user->nickname,
            'from_emmd5' => md5($user->email),
            'to' => $this->to,
            'time' => $this->time,
            'text' => $this->text,
            'type' => $this->type
        );
    }

    public function insert(): bool
    {
        $stmt = $this->conn->prepare("INSERT INTO messages (`from`, `to`, text, time, type) VALUES (?, ?, ?, ?, ?)");
        $this->time = (new DateTime())->format('Y-m-d H:i:s');
        $stmt->bind_param("iisss", $this->from, $this->to, $this->text, $this->time, $this->type);
        return $stmt->execute();
    }

    public static function add_reply_message($conn, $from, $sub, $in_blog, $text, $floor)
    {
        $blog = new Blog($conn, $in_blog, false);
        $blog->get_data();
        if ($sub) {
            $reply = new Reply($conn);
            $reply->in_blog = $in_blog;
            $reply->floor = $floor;
            $reply->query_by_floor();
            $to = $reply->owner;
        } else {
            $to = $blog->owner->uid;
        }
        if ($from == $to) return;
        $config = Info::config();
        $msg = self::of(
            $conn, $from, $to,
            '在帖子 <a href="'.get_url_prefix().$config['domain'].'/blog/show.php?id='.$in_blog.'&floor='.$floor.'">'.substr($blog->title, 0, 60).'</a> 里回复了你：'.$text,
            "reply");
        $msg->insert();
    }

    public static function add_reg_message_to_admins($conn, $user)
    {
        $sql = "SELECT uid FROM users WHERE admin > 0";
        $result = mysqli_query($conn, $sql);
        while ($arr = mysqli_fetch_array($result)) {
            $msg = self::of($conn, $user->uid, $arr['uid'], "于 ".$user->regtime." 注册了账号（仅管理员接收）", "reg-admin");
            $msg->insert();
        }
    }

    public static function add_system_boardcast($conn, $text)
    {
        $config = Info::config();
        $msg = self::of($conn, $config['test_acc_uid'], 0, $text, "sys-bc");
        $msg->insert();
    }

    public static function of($conn, $from, $to, $text, $type): Message
    {
        $msg = new Message($conn);
        $msg->from = $from;
        $msg->to = $to;
        $msg->type = $type;
        $msg->text = $text;
        return $msg;
    }

    public static function of_completed($conn, $from, $to, $text, $type, $time, $id): Message
    {
        $msg = new Message($conn);
        $msg->from = $from;
        $msg->to = $to;
        $msg->type = $type;
        $msg->text = $text;
        $msg->time = $time;
        $msg->id = $id;
        return $msg;
    }

    public static function get_from_user($conn, $uid, $page): array
    {
        $sql = 'SELECT COUNT(*) AS amount FROM messages WHERE `to` = 0 OR `to` = '.$uid;
        $cnt = mysqli_fetch_array(mysqli_query($conn, $sql))['amount'];
        $sql = 'SELECT * FROM messages WHERE `to` = '.$uid.' ORDER BY id DESC LIMIT '.($page * 20).', 20';
        $result = mysqli_query($conn, $sql);
        $list = array();
        while ($arr = mysqli_fetch_array($result)) {
            $msg = self::of_completed($conn, $arr['from'], $arr['to'], $arr['text'], $arr['type'], $arr['time'], $arr['id']);
            $list[] = $msg->to_json_array();
            $msg->set_to_read_state();
        }
        return array(
            'cnt' => $cnt,
            'list' => $list
        );
    }

    public function set_to_read_state()
    {
        $sql = "UPDATE messages SET be_read = 1 WHERE id = ".$this->id;
        mysqli_query($this->conn, $sql);
    }

    public static function get_user_messages($conn, $uid): int
    {
        $sql = 'SELECT COUNT(*) AS amount FROM messages WHERE be_read = 0 AND (`to` = 0 OR `to` = '.$uid.')';
        return mysqli_fetch_array(mysqli_query($conn, $sql))['amount'];
    }
}