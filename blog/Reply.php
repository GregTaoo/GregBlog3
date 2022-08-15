<?php
class Reply {
    public int $in_blog = 0, $floor = 0, $owner = 0, $reply_id = 0;
    public string $text, $time;
    public bool $sub = false;
    public int $sub_floor = 0, $sub_sum = 0;

    public mysqli $conn;

    public static string $sql = "INSERT INTO replies (in_blog, floor, owner, text, time) VALUES (?, ?, ?, ?, ?)";
    public static string $sql_sub = "INSERT INTO replies (in_blog, floor, owner, sub, sub_floor, text, time) VALUES (?, ?, ?, ?, ?, ?, ?)";

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function query_by_floor(): bool
    {
        $stmt = $this->conn->prepare("SELECT * FROM replies WHERE in_blog = ? AND floor = ? AND sub = 0");
        $stmt->bind_param("ii", $this->in_blog, $this->floor);
        $stmt->execute();
        if ($arr = mysqli_fetch_array($stmt->get_result())) {
            $this->owner = $arr['owner'];
            $this->text = $arr['text'];
            $this->sub_sum = $arr['sub_sum'];
            $this->time = $arr['time'];
            $this->reply_id = $arr['reply_id'];
            return true;
        }
        return false;
    }

    public function query(): bool
    {
        $stmt = $this->conn->prepare("SELECT * FROM replies WHERE in_blog = ? AND floor = ? AND sub = ? AND sub_floor = ?");
        $stmt->bind_param("iiii", $this->in_blog, $this->floor, $this->sub, $this->sub_floor);
        $stmt->execute();
        if ($arr = mysqli_fetch_array($stmt->get_result())) {
            $this->owner = $arr['owner'];
            $this->text = $arr['text'];
            $this->sub_sum = $arr['sub_sum'];
            $this->time = $arr['time'];
            $this->reply_id = $arr['reply_id'];
            return true;
        }
        return false;
    }

    public function delete(): bool
    {
        $stmt = $this->conn->prepare($this->sub ? "DELETE FROM replies WHERE in_blog = ? AND floor = ? AND sub = ? AND sub_floor = ?" : "DELETE FROM replies WHERE in_blog = ? AND floor = ?");
        if ($this->sub) {
            $stmt->bind_param("iiii", $this->in_blog, $this->floor, $this->sub, $this->sub_floor);
            $reply = new Reply($this->conn);
            $reply->in_blog = $this->in_blog;
            $reply->floor = $this->floor;
            if (!$reply->query_by_floor()) return false;
        }
        else {
            $stmt->bind_param("ii", $this->in_blog, $this->floor);
            $blog = new Blog($this->conn, $this->in_blog, false);
            $blog->get_data();
        }
        return $stmt->execute() && $this->sub ? $reply->decrease_subs_sum() : $blog->decrease_replies_sum();
    }

    public function get_sub_replies_list($page): array
    {
        $sql = "SELECT * FROM replies WHERE in_blog = ".$this->in_blog." AND sub = 1 AND floor = ".$this->floor." ORDER BY sub_floor DESC LIMIT ".($page * 5).", 5";
        $result = mysqli_query($this->conn, $sql);
        $list = array();
        while ($arr = mysqli_fetch_array($result)) {
            $reply = new Reply($this->conn);
            $reply->in_blog = $this->in_blog;
            $reply->floor = $arr['floor'];
            $reply->sub = $arr['sub'];
            $reply->sub_floor = $arr['sub_floor'];
            $reply->owner = $arr['owner'];
            $reply->text = $arr['text'];
            $reply->sub_sum = $arr['sub_sum'];
            $reply->time = $arr['time'];
            $reply->reply_id = $arr['reply_id'];
            $list[] = $reply;
        }
        return $list;
    }

    public function parse_emotions()
    {
        $this->text = preg_replace_callback("/\[([a-zA-Z\d_-]*)]/", function ($res) {
            $ret = '<img style="max-width:128px" src="';
            $config = Info::config();
            $lnk = $config['emotion_'.$res[1]];
            if (empty($lnk)) {
                return $res[0];
            }
            $http = substr($lnk, 0, 7);
            $lnk = $http == "http://" || $http == "https:/" ? $lnk : "https://unpkg.com/gregblog-cdn/img/".$lnk;
            $ret .= $lnk;
            $ret .= '" alt="emo" title="'.$res[1].'">';
            return $ret;
        }, $this->text);
    }

    public function to_json_array(): array
    {
        $user = new User($this->conn);
        $user->uid = $this->owner;
        $user->query("uid");
        $this->parse_emotions();
        $arr = array(
            'in_blog'=>$this->in_blog,
            'floor'=>$this->floor,
            'sub'=>$this->sub,
            'sub_floor'=>$this->sub_floor,
            'owner'=>$this->owner,
            'owner_admin'=>$user->admin,
            'owner_nickname'=>$user->nickname,
            'owner_emmd5'=>md5($user->email),
            'owner_title'=>User::get_title_label($this->conn, $user),
            'text'=>$this->get_parsed_text(),
            'sub_sum'=>$this->sub_sum,
            'time'=>$this->time,
            'reply_id'=>$this->reply_id
        );
        if (!$this->sub) {
            $subs = $this->get_sub_replies_list(0);
            $subs_json = array();
            foreach ($subs as $reply) {
                $subs_json[] = $reply->to_json_array();
            }
            $arr['subs'] = $subs_json;
        }
        return $arr;
    }

    public function send_at_msgs()
    {
        preg_replace_callback(
            "/ @([a-zA-Z-_\u4e00-\u9fa5]{1,64}) /",
            function ($result) {
                $user = new User($this->conn);
                $user->nickname = $result[1];
                $user->query("nickname");
                if ($user->exist) {
                    Message::add_at_message($this->conn, $this->owner, $user->uid, $this->in_blog, $this->text, $this->floor, $this->reply_id);
                }
                return $result;
            },
            $this->text, 10
        );
    }

    public function get_parsed_text(): string
    {
        return preg_replace_callback(
            "/ @([a-zA-Z-_\u4e00-\u9fa5]{1,64}) /",
            function ($result) {
                $user = new User($this->conn);
                $user->nickname = $result[1];
                $user->query("nickname");
                if ($user->exist) {
                    return '<a href="/user/space.php?uid='.$user->uid.'">'.$result[0].'</a>';
                }
                return $result;
            },
            $this->text, 10
        );
    }

    //pls provide $in_blog, $sub, if subs, provide $floor
    public function insert(): bool
    {
        if (!$this->sub) {
            $blog = new Blog($this->conn, $this->in_blog, false);
            $blog->get_data();
            $this->floor = $blog->replies_sum + 1;
        } else {
            $reply = new Reply($this->conn);
            $reply->in_blog = $this->in_blog;
            $reply->floor = $this->floor;
            if (!$reply->query_by_floor()) return false;
            $this->sub_floor = $reply->sub_sum + 1;
        }
        $this->time = (new DateTime())->format('Y-m-d H:i:s');
        $stmt = $this->conn->prepare($this->sub ? self::$sql_sub : self::$sql);
        if (!$this->sub) $stmt->bind_param("iiiss", $this->in_blog, $this->floor, $this->owner, $this->text, $this->time);
        else $stmt->bind_param("iiiiiss", $this->in_blog, $this->floor, $this->owner, $this->sub, $this->sub_floor, $this->text, $this->time);
        $statu = ($this->sub ? $reply->increase_subs_sum() : $blog->increase_replies_sum()) && $stmt->execute();
        if (!$statu) return false;
        $this->reply_id = mysqli_fetch_array(mysqli_query($this->conn, "SELECT LAST_INSERT_ID() AS id FROM replies"))['id'];
        $this->send_at_msgs();
        return true;
    }

    public static function get_json_from_blog($conn, $id, $page): array
    {
        $sql = "SELECT * FROM replies WHERE in_blog = ".$id." AND sub = 0 ORDER BY time DESC LIMIT ".($page * 20).", 20";
        $result = mysqli_query($conn, $sql);
        $list = array();
        while ($arr = mysqli_fetch_array($result)) {
            $reply = new Reply($conn);
            $reply->in_blog = $id;
            $reply->floor = $arr['floor'];
            $reply->sub = $arr['sub'];
            $reply->sub_floor = $arr['sub_floor'];
            $reply->owner = $arr['owner'];
            $reply->text = $arr['text'];
            $reply->sub_sum = $arr['sub_sum'];
            $reply->time = $arr['time'];
            $reply->reply_id = $arr['reply_id'];
            $list []= $reply->to_json_array();
        }
        return $list;
    }

    public static function get_sub_json_from_blog($conn, $in_blog, $floor, $page): array
    {
        $reply = new Reply($conn);
        $reply->in_blog = $in_blog;
        $reply->floor = $floor;
        $arr = array();
        foreach ($reply->get_sub_replies_list($page) as $sub) {
            $arr[] = $sub->to_json_array();
        }
        return $arr;
    }

    public function increase_subs_sum(): bool
    {
        $sql = "UPDATE replies SET sub_sum = sub_sum + 1 WHERE in_blog = ".$this->in_blog." AND floor = ".$this->floor." AND sub = 0";
        return mysqli_query($this->conn, $sql);
    }

    public function decrease_subs_sum(): bool
    {
        $sql = "UPDATE replies SET sub_sum = sub_sum - 1 WHERE in_blog = ".$this->in_blog." AND floor = ".$this->floor." AND sub = 0";
        return mysqli_query($this->conn, $sql);
    }

    public static function get_page_from_reply_id($conn, $id, $floor, $blogid, &$sub_page): int
    {
        $sub_page = -1;
        $sql = "SELECT sub FROM replies WHERE reply_id = ".$id;
        $result = mysqli_query($conn, $sql);
        $is_sub = mysqli_fetch_array($result)['sub'];
        if ($is_sub) {
            $sql = "SELECT COUNT(*) as cnt FROM replies WHERE sub = 1 AND reply_id >= ".$id." AND in_blog = ".$blogid." AND floor = ".$floor;
            $result = mysqli_query($conn, $sql);
            $cnt = mysqli_fetch_array($result)['cnt'];
            $sub_page = (int)($cnt / 5);
        }
        $sql = "SELECT COUNT(*) as cnt FROM replies WHERE sub = 0 AND reply_id >= ".$id." AND in_blog = ".$blogid;
        $result = mysqli_query($conn, $sql);
        $cnt = mysqli_fetch_array($result)['cnt'];
        return (int)($cnt / 20);
    }
}