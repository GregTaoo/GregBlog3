<?php
class Imgur {
    public int $id, $owner, $size;
    public string $type, $md5, $upload_time;
    public $content, $suffix;
    public mysqli $conn;

    public static array $allowed_types = array(
        "jpg", "png", "gif", "apng", "webp", "tiff", "bmp", "jpeg"
    );

    public static function check_type($file_name): bool
    {
        $arr = explode('.', $file_name);
        $ex = array_pop($arr);
        return in_array(strtolower($ex), self::$allowed_types);
    }

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function delete(): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM imgur WHERE md5 = ? AND id = ?");
        $stmt->bind_param("si", $this->md5, $this->id);
        return $this->delete_img_file() && $stmt->execute();
    }

    //pls provide $owner, $content, $size and $type before exec this func
    public function upload(): bool
    {
        $stmt = $this->conn->prepare("INSERT INTO imgur (owner, md5, upload_time, type, size, suffix) VALUES (?, ?, ?, ?, ?, ?)");
        $this->upload_time = (new DateTime())->format('Y-m-d H:i:s');
        $this->md5 = md5($this->owner."_".$this->upload_time."_".rand());
        $stmt->bind_param("isssis", $this->owner, $this->md5, $this->upload_time, $this->type, $this->size, $this->suffix);
        $success = $stmt->execute();
        if (!$success) return false;
        else {
            $this->id = $this->get_id();
            return $this->write_img_file();
        }
    }

    public function write_img_file(): bool
    {
        $config = Info::config();
        $path = $_SERVER['DOCUMENT_ROOT'].$config['imgur_file_path'].$this->id."_".$this->md5.".".$this->suffix;
        $file = fopen($path, "w");
        if (!fwrite($file, $this->content)) {
            return false;
        }
        fclose($file);
        return true;
    }

    public function delete_img_file(): bool
    {
        $config = Info::config();
        $path = $_SERVER['DOCUMENT_ROOT'].$config['imgur_file_path'].$this->id."_".$this->md5.".".$this->suffix;
        return !file_exists($path) || unlink($path);
    }

    public function get_id(): int
    {
        $stmt = $this->conn->prepare("SELECT id FROM imgur WHERE md5 = ? AND owner = ?");
        $stmt->bind_param("si", $this->md5, $this->owner);
        $stmt->execute();
        $this->id = mysqli_fetch_array($stmt->get_result())['id'];
        return $this->id;
    }

    public function get_data()
    {
        $stmt = $this->conn->prepare("SELECT * FROM imgur WHERE md5 = ? AND id = ?");
        $stmt->bind_param("si", $this->md5, $this->id);
        $stmt->execute();
        if (!$arr = mysqli_fetch_array($stmt->get_result())) die;
        $this->owner = $arr['owner'];
        $this->content = $arr['content'];
        $this->type = $arr['type'];
        $this->upload_time = $arr['upload_time'];
        $this->size = $arr['size'];
        $this->suffix = $arr['suffix'];
    }

    public function get_data_by_id()
    {
        $stmt = $this->conn->prepare("SELECT * FROM imgur WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        if (!$arr = mysqli_fetch_array($stmt->get_result())) die;
        $this->md5 = $arr['md5'];
        $this->owner = $arr['owner'];
        $this->type = $arr['type'];
        $this->upload_time = $arr['upload_time'];
        $this->size = $arr['size'];
        $this->suffix = $arr['suffix'];
    }

    public static function fetch($conn, $md5, $id): Imgur
    {
        $imgur = new Imgur($conn);
        $imgur->md5 = $md5;
        $imgur->id = $id;
        $imgur->get_data();
        return $imgur;
    }

    public static function fetch_by_id($conn, $id): Imgur
    {
        $imgur = new Imgur($conn);
        $imgur->id = $id;
        $imgur->get_data_by_id();
        return $imgur;
    }

    public static function get_list_by_uid($conn, $uid): array
    {
        $list = array();
        $stmt = $conn->prepare("SELECT id FROM imgur WHERE owner = ? ORDER BY id DESC");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($arr = mysqli_fetch_array($result)) {
            $list[] = self::fetch_by_id($conn, $arr['id']);
        }
        return $list;
    }
    
    public function to_json_array(): array
    {
        $config = Info::config();
        $user = User::get_user($this->conn, $this->owner, false);
        return array(
            'src' => get_url_prefix().$config['domain'].$config['imgur_file_path'].$this->id.'_'.$this->md5.'.'.$this->suffix,
            'time' => $this->upload_time,
            'size' => $this->size,
            'suffix' => $this->suffix,
            'md5' => $this->md5,
            'id' => $this->id,
            'owner' => $user->uid,
            'owner_nickname' => $user->nickname
        );
    }
    
    public static function get_json_by_page($conn, $page, $uid): array
    {
        $cnt = self::get_cnt($conn);
        $list = array();
        $sql = "SELECT id FROM imgur WHERE ".($uid > 0 ? "owner = ".$uid : "1")." ORDER BY id DESC LIMIT ".($page * 20).", 20";
        $result = mysqli_query($conn, $sql);
        while ($arr = mysqli_fetch_array($result)) {
            $list[] = self::fetch_by_id($conn, $arr['id'])->to_json_array();
        }
        return array(
            'cnt' => $cnt,
            'list' => $list
        );
    }

    public static function get_cnt($conn): int
    {
        $sql = "SELECT COUNT(*) AS amount FROM imgur WHERE 1";
        return mysqli_fetch_array(mysqli_query($conn, $sql))['amount'];
    }
}
