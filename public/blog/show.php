<?php
include '../include.php';
$id = empty($_GET['id']) || !is_numeric($_GET['id']) ? 0 : $_GET['id'];
$loader = new Loader("show-blog");
$config = Info::config();
$blog = new Blog($loader->info->conn, $id, true);
$blog->get_data();
$have_permsn = $blog->have_permission();
$loader->init( $blog->exist ? (!$blog->visible && !$have_permsn ? "此博客不可见" : $blog->title." - ".$blog->owner->nickname."的帖子") : "博客不存在");
Loader::add_css("../static/css/menu.css");
Loader::add_css("../static/css/blog.css");
if ($config['use_local_cdn'] == 1) {
    Loader::add_css(Loader::$local_cdn.'js/katex/katex.min.css');
    Loader::add_js(Loader::$local_cdn.'js/katex/katex.min.js');
    Loader::add_js(Loader::$local_cdn.'js/katex/contrib/mhchem.min.js');
    Loader::add_js(Loader::$local_cdn.'js/katex/contrib/auto-render.min.js');
    Loader::add_js(Loader::$local_cdn.'js/highlight/highlight.min.js');
    Loader::add_css(Loader::$local_cdn.'js/highlight/styles/atom-one-dark.min.css');
} else {
    Loader::add_css($config['katex_css_src']);
    Loader::add_js($config['katex_js_src']);
    Loader::add_js($config['katex_mhchem_ext_js_src']);
    Loader::add_js($config['katex_renderer_js_src']);
    Loader::add_js($config['highlight_js_src']);
    Loader::add_css($config['highlight_css_src']);
}
$iframe = empty($_GET['iframe']) ? 0 : $_GET['iframe'];
?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            renderMathInElement(document.body, {
                delimiters: [
                    {left: "$$", right: "$$", display: true},
                    {left: "$", right: "$", display: false}
                ]
            });
        });
        hljs.highlightAll();
    </script>
<?php
$loader->init_end();
if (!$blog->exist || (!$have_permsn && !$blog->visible)) {
    ?>
    <body>
    <?php if (!$iframe) $loader->top_menu(); ?>
    <div class="ui main container" style="margin-top: 64px">
        <div class="ui error message" id="error">
            <?php if (!$blog->exist) echo '此博客不存在'; else echo '此博客不可见'; ?>
        </div>
    </div>
    </body>
    <?php
    $loader->footer();
    die;
}
if ($iframe) {
    ?>
    <body>
    <div class="ui main container" style="margin-top: 10px">
        <div style="word-break: break-word; font-size: medium; margin: 20px" id="blog-main">
            <?php echo $blog->get_parsed_text(); ?>
        </div>
    </div>
    </body>
    <script>
        let tables = $("table");
        tables.addClass("ui celled table");
        tables.css("width", "100%");
    </script>
    <?php
    die;
}
$replies_pages = (int)(($blog->replies_sum - 1) / 20);
if (!User::viewed_blog($id)) {
    User::set_view_blog($id);
    $blog->increase_page_view();
}
$clted = false;
if (User::logged() && $id > 0) {
    $clt = new Collection($loader->info->conn, User::uid());
    $clted = $clt->collected($id, $time);
}
?>
    <body style="padding-bottom: 16px;">
    <?php $loader->top_menu(); ?>
    <div class="ui main container" style="margin-top: 64px">
        <div class="ui container">
            <div class="ui existing segment" style="font-size: x-large; overflow: hidden">
                <strong><?php echo substr($blog->title, 0, 60); ?></strong>
            </div>
            <div class="ui horizontal segments" style="overflow: hidden; word-break: break-all">
                <div class="ui segment">
                    <h2 class="ui header" style="text-align: center">
                        <a href="<?php echo get_url_prefix().$config['domain']."/user/space.php?uid=".$blog->owner->uid; ?>">
                            <?php echo $blog->owner->get_avatar_img(); ?>
                            <div class="sub header">
                                <?php echo $blog->owner->nickname.User::get_title_label($loader->info->conn, $blog->owner); ?>
                            </div>
                        </a>
                    </h2>
                </div>
                <div class="ui segment">
                    <i class="sticky note icon"></i> <?php echo $blog->intro ?><br>
                    <i class="tags icon"></i> <?php echo $blog->tags ?><br>
                    <i class="calendar alternate icon"></i> <?php echo $blog->create_time ?><br>
                    <i class="edit icon"></i> <?php echo $blog->latest_edit_time." By 
                                <a href=\"".get_url_prefix().$config['domain']."/user/space.php?uid=".$blog->latest_editor->uid."\">".$blog->latest_editor->nickname."</a>
                            "?><br>
                    <?php if (!$blog->visible) echo '此博客对无权限者不可见' ?>
                </div>
            </div>
            <div class="ui teal segment">
                <i class="eye icon"></i> <?php echo $blog->page_view; ?> |
                <i class="comment icon"></i> <?php echo $blog->replies_sum; ?> |
                <i class="star icon"></i> <?php echo $blog->likes; ?> |
                <?php echo $clted ? '<div class="ui mini button" id="clt-btn" onclick="dis_collect()"><i class="star icon"></i>已收藏</div>' : '<div class="ui mini yellow button" id="clt-btn" onclick="add_collect()"><i class="star icon"></i>收藏</div>';?>
            </div>
        </div>
        <div class="ui grid" style="margin-top: 3px">
            <div class="sixteen wide mobile thirteen wide computer column" id="context">
                <div class="ui container">
                    <div class="ui existing segment" style="word-break: break-word; font-size: medium" id="blog-main">
                        <?php echo $blog->get_parsed_text(); ?>
                    </div>
                    <div class="ui existing segment" style="word-break: break-word; font-size: medium; margin-bottom: -16px">
                        <div class="ui form" style="word-break: break-word; font-size: medium">
                            <label class="field">
                                <textarea id="reply-textarea" rows="3"></textarea>
                            </label>
                            <div class="ui primary submit labeled icon button" id="reply-button" onclick="post_reply(0, false)" style="margin-top: 16px">
                                <i class="icon edit"></i>
                                添加评论
                            </div>
                            <div class="ui teal labeled icon button emotion-toggle" style="float: right; margin-top: 16px">
                                <i class="icon smile outline"></i>表情
                            </div>
                            <div class="ui fluid popup" style="max-height: 300px; max-width: 350px; overflow-y: auto; overflow-x: hidden;">
                                <table id="emotions-select-main" class="ui very basic collapsing celled table"></table>
                            </div>
                        </div>
                        <h3 class="ui header">评论区</h3>
                        <div class="ui message" style="font-size: small">
                            <?php
                            echo $blog->replies_sum == 0 ? "还没有评论，赶快抢占沙发吧！" : "共有 ".$blog->replies_sum." 条评论";
                            ?>
                        </div>
                        <div class="ui threaded comments" id="blog-replies">
                        </div>
                        <div class="ui threaded comments" id="reply-page-selector" <?php if ($blog->replies_sum == 0) echo 'style="display: none"' ?>>
                        </div>
                    </div>
                    <div class="ui modal" id="delete-modal">
                        <div class="header">
                            提示
                        </div>
                        <div class="image content">
                            <div class="image">
                                <i class="exclamation triangle icon"></i>
                            </div>
                            <div class="description">
                                <h3>确认要删除此博客吗？请确认是否有尚未保存的内容。</h3>
                            </div>
                        </div>
                        <div class="actions">
                            <div class="ui red button" onclick="hide_delete_modal('delete-modal')">取消</div>
                            <div class="ui green button" onclick="delete_blog(<?php echo $id; ?>)">确认删除</div>
                        </div>
                    </div>
                    <div class="ui mini modal" id="delete-reply-modal">
                        <div class="header">
                            提示
                        </div>
                        <div class="content">
                            确认要删除此回复吗？
                        </div>
                        <div class="actions">
                            <div class="ui red button" onclick="hide_delete_modal('delete-reply-modal')">取消</div>
                            <div class="ui green button" id="submit">确认删除</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="three wide computer sixteen wide mobile column">
                <div class="ui sticky container" id="side-bar">
                    <?php
                    if ($have_permsn) {
                        ?>
                        <div class="ui blue segment">
                            <div class="ui two buttons" style="text-align: center">
                                <a class="ui animated fade button" href="./post.php?is_edit=1&id=<?php echo $id ?>">
                                    <div class="visible content">
                                        <i class="edit icon"></i>
                                    </div>
                                    <div class="hidden content">编辑</div>
                                </a>
                                <?php
                                if (!$blog->is_editor) {
                                    ?>
                                    <div class="ui animated fade red button" onclick="show_modal('delete-modal')">
                                        <div class="visible content">
                                            <i class="trash alternate icon"></i>
                                        </div>
                                        <div class="hidden content">删除</div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <a class="ui fluid button" href="#reply-textarea">
                        <i class="comments icon"></i>
                        评论区
                    </a>
                    <div class="ui top attached block header">
                        推荐
                    </div>
                    <div class="ui loading bottom attached segment" id="recommend">
                        loading...
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    $floor = empty($_GET['floor']) || !is_numeric($_GET['id']) ? -1 : $_GET['floor'];
    $reply_id = empty($_GET['replyid']) || !is_numeric($_GET['id']) ? -1 : $_GET['replyid'];
    $parse_page = $reply_id > 0;
    $newpage = $sub_page = -1;
    if ($parse_page) {
        $newpage = Reply::get_page_from_reply_id($loader->info->conn, $reply_id, $floor, $id, $sub_page);
    }
    $loader->footer();
    ?>
    <div class="rocket" onclick="scroll2top()" id="rocket">
        <img src="../static/img/rocket.png" alt="TOP" style="width: 40px">
    </div>
    </body>
    <script>
        let local_uid = <?php echo User::uid(); ?>;
        let admin = <?php echo User::admin(); ?>;
        let pages = <?php echo $replies_pages; ?>;
        let blog_id = <?php echo $id; ?>;
        let uid = <?php echo $blog->owner->uid ?>;
        let owner_label = <?php echo "'".User::get_owner_label()."'" ?>;
        let admin_label = <?php echo "'".User::get_admin_label()."'" ?>;
        let avatar = <?php echo "'".$config['def_user_avatar']."'" ?>;
        let curpage = 0;
        let sub_curpage = new Map();
        let reply_id = <?php echo $reply_id ?>;
        let turn_to_subpage = <?php echo $sub_page ?>;
        let turn_to_floor = <?php echo $floor ?>;
        let tables = $("table");
        tables.addClass("ui celled table");
        tables.css("width", "100%");
        <?php
            if ($newpage >= 0) echo 'curpage = '.$newpage.';';
            if ($sub_page >= 0) {
                echo 'sub_curpage.set('.$floor.', '.$sub_page.');';
            }
            echo 'console.log('.$newpage.','.$sub_page.');';
        ?>
    </script>
    <script src="../static/js/blog/show.js">
    </script>
<?php
$loader->page_end();