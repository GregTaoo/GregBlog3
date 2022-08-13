<?php
include '../include.php';
$loader = new Loader("search");
$loader->init("搜索");
Loader::add_css("../static/css/menu.css");
$loader->init_end();
$keyw = empty($_GET['keyw']) ? "" : htmlspecialchars($_GET['keyw']);
$config = Info::config();
?>
    <body>
    <?php $loader->top_menu(); ?>
    <div class="ui main container" style="margin-top: 64px">
        <div class="ui segment">
            <div class="ui form">
                <div class="two fields">
                    <div class="field">
                        <div class="ui input">
                            <input type="text" id="keyw" value="<?php echo $keyw; ?>" placeholder="搜点啥吧">
                        </div>
                    </div>
                    <div class="field">
                        <div class="ui primary button" onclick="search(0)" id="btn"><i class="search icon"></i>搜索</div>
                    </div>
                </div>
                排序方式：
                <div class="ui selection dropdown" id="orderby" style="margin-right: 20px">
                    <div class="text">排序方式</div>
                    <i class="dropdown icon"></i>
                </div>
                <div class="ui toggle checkbox">
                    <input type="checkbox" id="desc">
                    <label>从小到大</label>
                </div>
            </div>
        </div>
        <div class="ui pointing menu" id="type-menu">
            <a class="item" id="type-blog" onclick="set_type('blog')">
                博客
            </a>
            <a class="item" id="type-user" onclick="set_type('user')">
                用户
            </a>
        </div>
        <div class="ui segment" id="content-div">
            <div class="ui small header" id="hdr"><?php echo empty($keyw) ? "搜索点啥儿？" : "找不到结果" ?></div>
            <div class="ui divider"></div>
            <div class="ui link two doubling cards" id="content"></div>
            <div id="page-selector"></div>
        </div>
    </div>
    <?php $loader->footer(); ?>
    </body>
    <script>
        let def_avatar = <?php echo "'".$config['def_user_avatar']."'" ?>;
    </script>
    <script src="../static/js/search/search_index.js">
    </script>
    <script>
        <?php if (!empty($keyw)) echo 'search(0);'; ?>
    </script>
<?php
$loader->page_end();