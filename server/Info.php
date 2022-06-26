<?php
class Info {
    public static string $version = "1.0.1";

    public static string $mysql_password = " ";
    public static string $mysql_username = " ";
    public static string $mysql_ip = " ";
    public static string $mysql_database = " ";
    public static string $domain = " ";
    //public static string $domain = "localhost";

    public mysqli $conn;

    public int $blogs_sum = -1;

    public function __construct()
    {
        $this -> conn = mysqli_connect(Info::$mysql_ip, Info::$mysql_username, Info::$mysql_password, Info::$mysql_database);
    }

    public function get_site_data()
    {
        $sql = "SELECT * FROM site WHERE 1";
        $arr = mysqli_fetch_array(mysqli_query($this -> conn, $sql));
        $this -> blogs_sum = $arr['blogs_sum'];
    }

    public function increase_num($str, $amount): bool
    {
        $sql = "UPDATE site SET ".$str." = ".$str." + ".$amount." WHERE 1";
        return mysqli_query($this -> conn, $sql);
    }

    public static function config(): array
    {
        return require($_SERVER['DOCUMENT_ROOT'].'/../server/Config.php');
    }
}