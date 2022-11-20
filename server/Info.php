<?php
class Info {
    public static string $version = "3.3.2.0";

    public mysqli $conn;

    public int $blogs_sum = -1;

    public function __construct()
    {
        $config = self::config();
        $this->conn = mysqli_connect($config['mysql_ip'], $config['mysql_username'], $config['mysql_password'], $config['mysql_database']);
    }

    public function get_site_data()
    {
        $sql = "SELECT * FROM site WHERE 1";
        $arr = mysqli_fetch_array(mysqli_query($this->conn, $sql));
        $this->blogs_sum = $arr['blogs_sum'];
    }

    public function increase_num($str, $amount): bool
    {
        $sql = "UPDATE site SET ".$str." = ".$str." + ".$amount." WHERE 1";
        return mysqli_query($this->conn, $sql);
    }

    public static function config(): array
    {
        try {
            $config = (array) json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../config/Config.json'));
            $backup = (array) json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../server/ConfigBackup.json'));
            return array_merge($backup, $config);
        } catch (Exception $e) {
            return json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../server/ConfigBackup.json'));
        }
        /*
        try {
            return require($_SERVER['DOCUMENT_ROOT'] . '/../server/Config.php');
        } catch (Exception $e) {
            return require($_SERVER['DOCUMENT_ROOT'] . '/../server/ConfigBackup.php');
        }
        */
    }
}