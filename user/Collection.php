<?php
class Collection {
    public int $uid, $cnt;
    public string $json;
    public mysqli $conn;

    public function __construct($conn, $uid)
    {
        $this->uid = $uid;
        $this->conn = $conn;
        $sql = "SELECT cnt, `json` FROM collections WHERE uid = ".$uid;
        $arr = mysqli_fetch_array(mysqli_query($conn, $sql));
        if (!empty($arr['cnt'])) {
            $this->cnt = $arr['cnt'];
            $this->json = $arr['json'];
        } else {
            User::create_collection($this->conn, $this->uid);
            $this->cnt = 0;
            $this->json = '{}';
        }
    }

    public static function get_by_user($conn, $uid, $page): array
    {
        $clt = new Collection($conn, $uid);
        $json = json_decode($clt->json, true);
        $arr = array_slice($json, $page * 20, 20);
        $list = array();
        foreach ($arr as $key=>$val) {
            $blog = new Blog($conn, substr($key, 1), false);
            $blog->get_data();
            $list []= $blog->to_json_array();
        }
        return array(
            'cnt' => $clt->cnt,
            'list' => $list
        );
    }

    public function collected($blogid, &$time): bool
    {
        $stmt = "SELECT JSON_EXTRACT(json, '$.b".$blogid."') AS `time` FROM collections WHERE uid = ".$this->uid;
        $result = mysqli_query($this->conn, $stmt);
        if ($arr = mysqli_fetch_array($result)) {
            if (!empty($arr['time'])) {
                $time = $arr['time'];
                return true;
            }
        }
        return false;
    }

    public function add_collect(?Blog $blog, &$err = ''): bool
    {
        if ($this->collected($blog->id, $time)) {
            $err = '已经于 '.$time.' 收藏过';
            return false;
        }
        if (!$blog->increase_likes()) return false;
        $time = (new DateTime()) -> format('Y-m-d H:i:s');
        $stmt = $this->conn->prepare("UPDATE collections SET json = JSON_INSERT(json, CONCAT('$.b', ?, ''), ?), cnt = cnt + 1 WHERE uid = ?");
        $uid = User::uid();
        $stmt->bind_param("ssi", $blog->id, $time, $uid);
        return $stmt->execute();
    }

    public function dis_collect(?Blog $blog, &$err = ''): bool
    {
        if (!$this->collected($blog->id, $time)) {
            $err = '尚未收藏过，怎么重复移除啊..';
            return false;
        }
        if (!$blog->decrease_likes()) return false;
        $stmt = $this->conn->prepare("UPDATE collections SET json = JSON_REMOVE(json, CONCAT('$.b', ?, '')), cnt = cnt - 1 WHERE uid = ?");
        $uid = User::uid();
        $stmt->bind_param("si", $blog->id, $uid);
        return $stmt->execute();
    }
}