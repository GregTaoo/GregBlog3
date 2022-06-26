<?php
class SearchEngine {
    public static array $allowed_order = array(
        "create_time", "id", "page_view", "replies_sum", "latest_edit_time"
    );

    public static function search_blog(?mysqli $conn, $page, $orderby, $keyw): array
    {
        $sql = "SELECT id FROM blogs 
           WHERE visible = 1 AND ((title LIKE '%".$keyw."%') OR (tags LIKE '%".$keyw."%') OR (intro LIKE '%".$keyw."%'))
           ORDER BY ".$orderby." LIMIT ".($page * 20).", 20";
        $list = array();
        $result = mysqli_query($conn, $sql);
        while ($arr = mysqli_fetch_array($result)) {
            $blog = new Blog($conn, $arr['id'], false);
            $blog->get_data();
            $list[] = $blog->to_json_array();
        }
        return array(
            'cnt' => self::search_blog_count($conn, $keyw),
            'list' => $list
        );
    }

    public static function search_blog_count(?mysqli $conn, $keyw): int
    {
        $sql_cnt = "SELECT COUNT(*) AS amount FROM blogs 
           WHERE visible = 1 AND ((title LIKE '%".$keyw."%') OR (tags LIKE '%".$keyw."%') OR (intro LIKE '%".$keyw."%'))";
        $arr_cnt = mysqli_fetch_array(mysqli_query($conn, $sql_cnt));
        return $arr_cnt['amount'];
    }

    public static function search_user(?mysqli $conn, $page, $keyw): array
    {
        $num = is_numeric($keyw);
        $stmt = $conn->prepare("SELECT uid FROM users WHERE allow_be_srch = 1 AND nickname LIKE CONCAT('%', ?, '%')".($num ? " OR uid = ".$keyw : "")." LIMIT ?, 20");
        $top = 20 * $page;
        $stmt->bind_param("si", $keyw, $top);
        $stmt->execute();
        $list = array();
        $result = $stmt->get_result();
        while ($arr = mysqli_fetch_array($result)) {
            $user = User::get_user($conn, $arr['uid'], false);
            $list[] = $user->get_json_array();
        }
        return array(
            'cnt' => self::search_user_count($conn, $keyw),
            'list' => $list
        );
    }

    public static function search_user_count(?mysqli $conn, $keyw): int
    {
        $num = is_numeric($keyw);
        $stmt = $conn->prepare("SELECT COUNT(*) AS amount FROM users WHERE allow_be_srch = 1 AND nickname LIKE CONCAT('%', ?, '%')".($num ? " OR uid = ".$keyw : ""));
        $stmt->bind_param("s", $keyw);
        $stmt->execute();
        $arr = mysqli_fetch_array($stmt->get_result());
        return $arr['amount'];
    }
}
