<?php
include '../include.php';
$loader = new Loader("imgur-api");
$mode = empty($_GET['mode']) ? "" : $_GET['mode'];
switch ($mode) {
    case "delete": {
        if (empty($_GET['md5']) || empty($_GET['id'])) die;
        $imgur = Imgur::fetch($loader -> info -> conn, $_GET['md5'], $_GET['id']);
        if (!User::logged() || $_SESSION['uid'] != $imgur -> owner && !$_SESSION['admin']) die("你没有权限");
        die($imgur -> delete() ? "success" : "删除失败");
    }
}
