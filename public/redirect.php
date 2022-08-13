<?php
include './include.php';
$loader = new Loader("redirect");
$loader->init("重定向页");
Loader::add_css("../static/css/menu.css");
$loader->init_end();
$not_jump = $_GET['jump'] == "0";
$button = $_GET['button'] == "1";
$msg = empty($_GET['msg']) ? "?" : $_GET['msg'];
$to = empty($_GET['to']) ? "/" : cur_url_decode($_GET['to']);
?>
<body>
<?php $loader->top_menu(); ?>
<div class="ui main container" style="margin-top: 64px; ">
    <div class="ui icon message">
        <div class="content">
            <div class="header">
                提示
            </div>
            <p><?php echo $msg == "url" ? "非本站链接，请谨慎前往：" : $msg; ?></p>
            <?php
            if (!$not_jump) echo '3秒后跳转';
            if ($button) echo '<a href="'.$to.'">点击跳转</a>'
            ?>
        </div>
    </div>
</div>
<?php $loader->footer(); ?>
</body>
<script>
    <?php if (!$not_jump) { ?>
    let url = '<?php echo $to; ?>';
    setTimeout(function () {
        window.location.href = url;
    }, 3000);
    <?php } ?>
</script>
<?php
$loader->page_end();
