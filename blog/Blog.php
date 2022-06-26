<?php
class Blog {
    public int $id, $replies_sum, $page_view = 0, $likes = 0;
    public bool $exist = false, $get_text, $visible, $is_editor = false;
    public User $owner, $latest_editor;
    public string $create_time, $latest_edit_time, $md;
    public string $origin_text, $title, $tags, $intro, $partition;
    public string $editors;
    public array $editors_arr;

    public mysqli $conn;
    public static string $sql = "SELECT owner, create_time, latest_edit_time, latest_editor, title, parti, visible, md5, replies_sum, page_view, tags, intro, likes FROM blogs WHERE id = ?";
    public static string $sql_full = "SELECT * FROM blogs WHERE id = ?";

    public function __construct($conn, $id, $get_text)
    {
        $this -> conn = $conn;
        $this -> id = $id;
        $this -> get_text = $get_text;
    }

    public function to_json_array(): array
    {
        if (!$this->exist) return array();
        return array(
            'id' => $this->id,
            'replies_sum' => $this->replies_sum,
            'page_view' => $this->page_view,
            'visible' => $this->visible,
            'owner' => $this->owner->uid,
            'owner_nickname' => $this->owner->nickname,
            'owner_emmd5' => md5($this->owner->email),
            'title' => $this->title,
            'tags' => $this->tags,
            'create_time' => $this->create_time,
            'latest_edit_time' => $this->latest_edit_time,
            'latest_editor' => $this->latest_editor->uid,
            'latest_editor_nickname' => $this->latest_editor->nickname,
            'intro' => $this->intro,
            'likes' => $this->likes,
            'parti' => $this->partition
        );
    }

    public static function randomly_select_as_json_list($info, $amount): array
    {
        $list = array();
        $num = array();
        $info->get_site_data();
        for ($i = 1; $i <= $amount; ++$i) {
            try {
                do {
                    $random = random_int(1, $info->blogs_sum);
                } while (in_array($random, $num));
                $blog = new Blog($info->conn, $random, false);
            } catch (Exception $e) {
                return array();
            }
            $blog->get_data();
            if (!$blog->exist || !$blog->visible) {
                $i--;
                continue;
            }
            $num[] = $random;
            $list[] = $blog->to_json_array();
        }
        return $list;
    }

    public static function select_by_rank_as_json($conn, $amount): array
    {
        $list = array();
        $sql = "SELECT id FROM blogs WHERE visible = 1 ORDER BY page_view DESC LIMIT 0, ".$amount;
        $result = mysqli_query($conn, $sql);
        while ($arr = mysqli_fetch_array($result)) {
            $blog = new Blog($conn, $arr['id'], false);
            $blog->get_data();
            $list[] = $blog->to_json_array();
        }
        return $list;
    }

    public function get_data()
    {
        $stmt = $this -> conn -> prepare($this -> get_text ? self::$sql_full : self::$sql);
        $stmt -> bind_param("i", $this -> id);
        $stmt -> execute();
        if ($arr = mysqli_fetch_array($stmt -> get_result())) {
            $this -> exist = true;
            $this -> owner = new User($this -> conn);
            $this -> owner -> uid = $arr['owner'];
            $this -> owner -> query(false);
            $this -> create_time = $arr['create_time'];
            $this -> latest_edit_time = $arr['latest_edit_time'];
            $this -> latest_editor = new User($this -> conn);
            $this -> latest_editor -> uid = $arr['latest_editor'];
            $this -> latest_editor -> query(false);
            $this -> title = $arr['title'];
            $this -> partition = $arr['parti'];
            $this -> visible = $arr['visible'];
            $this -> md = $arr['md5'];
            $this -> replies_sum = $arr['replies_sum'];
            $this -> tags = $arr['tags'];
            $this -> intro = $arr['intro'];
            $this->likes = $arr['likes'];
            $this->page_view = $arr['page_view'];
            if ($this -> get_text) {
                $this -> editors = $arr['editors'];
                $this -> read_blog_file();
                $this -> execute_editors();
            }
        }
    }

    public function get_parsed_text(): string
    {
        $text = $this->origin_text;
        $parsedown = new GregBlogParser();
        $text = $parsedown -> text(htmlspecialchars_decode($text));
        return $text;
    }

    public function create_blog(): bool
    {
        $config = Info::config();
        if ($this -> exist) return false;
        $stmt = $this -> conn -> prepare("INSERT INTO blogs (id, owner, create_time, latest_edit_time, editors, latest_editor, md5, title, tags, parti, visible, intro)
                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $info = new Info();
        $info -> get_site_data();
        if ($info -> blogs_sum == -1) return false;
        $this -> id = $info -> blogs_sum + 1;
        $this -> md = $this -> id."_".md5($this -> title."_".$this -> id."_".rand());
        $time = (new DateTime()) -> format('Y-m-d H:i:s');
        $this -> intro = empty($this -> intro) ? $config['def_blog_desc'] : $this -> intro;
        $stmt -> bind_param("iisssissssis", $this -> id, $this -> owner -> uid, $time, $time, $this -> editors, $this -> owner -> uid, $this -> md, $this -> title, $this -> tags, $this -> partition, $this -> visible, $this -> intro);
        $this->origin_text = self::strip_tags_except_code($this->origin_text);
        return $this -> write_blog_file() && $stmt -> execute() && $info -> increase_num("blogs_sum", 1);
    }

    public static function strip_tags_except_code($text): string
    {
        $arr = explode("```", $text);
        $st = substr($text, 0, 3) == '```';
        $result = $st ? '```' : '';
        $cnt = count($arr);
        for (;$st < $cnt; $st += 2) {
            $arr[$st] = strip_tags($arr[$st]);
        }
        for ($i = 0; $i < $cnt - 1; $i++) {
            $result = $result.$arr[$i].'```';
        }
        if ($cnt > 0) $result = $result.$arr[$cnt - 1];
        return $result;
    }

    public function update(): bool
    {
        if (!$this -> exist) return false;
        $stmt = $this -> conn -> prepare("UPDATE blogs SET latest_edit_time = ?, editors = ?, latest_editor = ?, title = ?, tags = ?, visible = ?, intro = ? WHERE id = ?");
        $time = (new DateTime()) -> format('Y-m-d H:i:s');
        $stmt -> bind_param("ssissisi", $time, $this -> editors, $_SESSION['uid'], $this -> title, $this -> tags, $this -> visible, $this -> intro, $this -> id);
        $this->origin_text = self::strip_tags_except_code($this->origin_text);
        return $stmt -> execute() && $this -> write_blog_file();
    }

    public function write_blog_file(): bool
    {
        $config = Info::config();
        $path = $_SERVER['DOCUMENT_ROOT'].$config['blog_file_path'].$this -> md.".md";
        $file = fopen($path, "w");
        if (!fwrite($file, $this -> origin_text)) {
            return false;
        }
        //var_dump(fwrite($file, $this -> origin_text));
        fclose($file);
        return true;
    }

    public function read_blog_file()
    {
        /*
        $path = $_SERVER['DOCUMENT_ROOT'] . Info::$blog_file_path . $this->md . ".md";
        $file = fopen($path, "r");
        $this -> origin_text = fread($file, filesize($path));
        fclose($file);
        */
        $config = Info::config();
        $this -> origin_text = file_get_contents("http://".Info::$domain.$config['blog_file_path'].$this -> md.".md");
    }

    public function execute_editors()
    {
        $splitted = explode(",", $this -> editors);
        $this -> editors_arr = preg_grep("/\d+/", $splitted);
    }

    public function have_permission(): bool
    {
        if (!User::logged() || !User::verified() || !$this -> exist) return false;
        if ($this -> owner -> uid == $_SESSION['uid'] || $_SESSION['admin'] > 0) return true;
        foreach ($this -> editors_arr as $uid) {
            if ($uid == $_SESSION['uid']) {
                $this -> is_editor = true;
                return true;
            }
        }
        return false;
    }

    public function delete()
    {
        if (!$this -> exist) return false;
        $sql = "DELETE FROM blogs WHERE id = ".$this -> id;
        return mysqli_query($this -> conn, $sql);
    }

    public static function get_blog_list($conn, $uid, $page, $self): array
    {
        $sql = "SELECT id FROM blogs WHERE owner = ".$uid.($self ? "" : " AND visible = 1")." ORDER BY id DESC LIMIT ".($page * 20).", 20";
        $result = mysqli_query($conn, $sql);
        $list = array();
        while ($arr = mysqli_fetch_array($result)) {
            $blog = new Blog($conn, $arr['id'], false);
            $blog -> get_data();
            $list[] = $blog;
        }
        return $list;
    }

    public static function get_admin_blog_json_list($conn, $page): array
    {
        $sql_cnt = "SELECT COUNT(*) AS cnt FROM blogs";
        $arr = mysqli_fetch_array(mysqli_query($conn, $sql_cnt));
        $cnt = $arr['cnt'];
        $sql = "SELECT id FROM blogs ORDER BY id DESC LIMIT ".($page * 20).", 20";
        $result = mysqli_query($conn, $sql);
        $list = array();
        while ($arr = mysqli_fetch_array($result)) {
            $blog = new Blog($conn, $arr['id'], false);
            $blog -> get_data();
            $list[] = $blog->to_json_array();
        }
        return array(
            'cnt' => $cnt,
            'list' => $list
        );
    }

    public static function get_blog_json_list($conn, $uid, $page, $self): array
    {
        $list = array();
        foreach (self::get_blog_list($conn, $uid, $page, $self) as $blog) {
            $list[] = $blog->to_json_array();
        }
        return array(
            'cnt' => self::get_user_blogs_sum($conn, $uid, $self),
            'list' => $list
        );
    }

    public static function get_user_blogs_sum($conn, $uid, $self): int
    {
        $sql = "SELECT COUNT(*) AS amount FROM blogs WHERE owner = ".$uid.($self ? "" : " AND visible = 1");
        return mysqli_fetch_array(mysqli_query($conn, $sql))['amount'];
    }

    public function increase_replies_sum(): bool
    {
        $sql = "UPDATE blogs SET replies_sum = replies_sum + 1 WHERE id = ".$this->id;
        return mysqli_query($this->conn, $sql);
    }

    public function decrease_replies_sum(): bool
    {
        $sql = "UPDATE blogs SET replies_sum = replies_sum - 1 WHERE id = ".$this->id;
        return mysqli_query($this->conn, $sql);
    }

    public function increase_page_view(): bool
    {
        $sql = "UPDATE blogs SET page_view = page_view + 1 WHERE id = ".$this->id;
        return mysqli_query($this->conn, $sql);
    }

    public function increase_likes(): bool
    {
        $sql = "UPDATE blogs SET likes = likes + 1 WHERE id = ".$this->id;
        return mysqli_query($this->conn, $sql);
    }

    public function decrease_likes(): bool
    {
        $sql = "UPDATE blogs SET likes = likes - 1 WHERE id = ".$this->id;
        return mysqli_query($this->conn, $sql);
    }
}