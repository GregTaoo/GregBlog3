<?php
include '../include.php';
$loader = new Loader("search-api");
if (empty($_GET['type']) || empty($_GET['keyw']) || !check_length($_GET['keyw'], 1, 512)) die;
$type = $_GET['type'];
$keyw = mysqli_escape_string($loader->info->conn, htmlspecialchars($_GET['keyw']));
$orderby = empty($_GET['orderby']) ? "page_view" : $_GET['orderby'];
if (!in_array($orderby, SearchEngine::$allowed_order)) die;
$desc = empty($_GET['desc']) || $_GET['desc'] == "true";
$page = empty($_GET['page']) ? 0 : $_GET['page'];
if (!is_numeric($page)) die;
switch ($type) {
    case "blog": {
        $list = SearchEngine::search_blog($loader->info->conn, $page, $orderby.($desc ? ' DESC' : ''), $keyw);
        break;
    }
    case "user": {
        $list = SearchEngine::search_user($loader->info->conn, $page, $keyw);
        break;
    }
    default: {
        die;
    }
}
die(json_encode($list));