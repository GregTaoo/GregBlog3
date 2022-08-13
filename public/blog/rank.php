<?php
include '../include.php';
$loader = new Loader("blog-rank");
$loader->init("热度排行榜");
Loader::add_css("../static/css/menu.css");
$loader->init_end();
?>
    <body>
    <?php $loader->top_menu(); ?>
    <div class="ui main container" style="margin-top: 64px">
        <div class="ui segment" style="margin-top: 20px">
            <h2>排行榜 <a class="ui orange button" href="../index.php">回到首页</a></h2>
            <div class="ui link two cards" id="rank">
            </div>
        </div>
    </div>
    <?php $loader->footer(); ?>
    </body>
    <script src="../static/js/blog/rank.js">
    </script>
<?php
$loader->page_end();