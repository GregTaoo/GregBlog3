<?php
include '../include.php';
$id = empty($_GET['id']) ? 0 : $_GET['id'];
$loader = new Loader("show-blog");
$config = Info::config();
$blog = new Blog($loader->info->conn, $id, true);
$blog->get_data();
$have_permsn = $blog->have_permission();
$loader->init( $blog->exist ? (!$blog->visible && !$have_permsn ? "此博客不可见" : $blog->title." - ".$blog->owner->nickname."的帖子") : "博客不存在");
Loader::add_css("../static/css/menu.css");
Loader::add_css("../static/css/blog.css");
Loader::add_css($config['katex_css_src']);
Loader::add_js($config['katex_js_src']);
Loader::add_js($config['katex_mhchem_ext_js_src']);
Loader::add_js($config['katex_renderer_js_src']);
Loader::add_js($config['highlight_js_src']);
Loader::add_css($config['highlight_css_src']);
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
    <?php $loader->top_menu(); ?>
    <div class="ui main container" style="margin-top: 64px">
        <div class="ui error message" id="error">
            <?php if (!$blog->exist) echo '此博客不存在'; else echo '此博客不可见'; ?>
        </div>
    </div>
    </body>
    <?php
    die;
}
$replies_pages = (int)(($blog->replies_sum - 1) / 20);
if (!User::viewed_blog($id)) {
    User::set_view_blog($id);
    $blog->increase_page_view();
}
$clted = false;
if (User::logged()) {
    $clt = new Collection($loader->info->conn, User::uid());
    $clted = $clt->collected($id, $time);
}
?>
    <body style="padding-bottom: 16px;">
    <?php $loader->top_menu(); ?>
    <div class="ui main container" style="margin-top: 64px">
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
        <?php
        if ($have_permsn) {
            ?>
            <div class="ui blue segment">
                <a class="ui button" href="./post.php?is_edit=1&id=<?php echo $id ?>">
                    <i class="edit icon"></i>
                    编辑
                </a>
                <?php
                if (!$blog->is_editor) {
                    ?>
                    <div class="ui button" onclick="show_modal('delete-modal')">
                        <i class="trash alternate icon"></i>
                        删除
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        }
        ?>
        <div class="ui existing segment" style="word-break: break-word; font-size: medium" id="blog-main">
            <?php echo $blog->get_parsed_text(); ?>
        </div>
        <div class="ui existing segment" style="word-break: break-word; font-size: medium">
            <div class="ui form" style="word-break: break-word; font-size: medium">
                <label class="field">
                    <textarea id="reply-textarea" rows="3"></textarea>
                </label>
                <div class="ui primary submit labeled icon button" id="reply-button" onclick="post_reply(0, false)" style="margin-top: 16px">
                    <i class="icon edit"></i>
                    添加评论
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
                    <h3>确认要删除此博客吗？请确认是否有尚未保存的内容。数据丢失本站一概不负责。</h3>
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
    <?php $loader->footer(); ?>
    <div class="rocket" onclick="scroll2top()" id="rocket">
        <img src="/static/img/rocket.png" alt="TOP" style="width: 40px">
    </div>
    </body>
    <script>
        let tables = $("table");
        tables.addClass("ui celled table");
        tables.css("width", "100%");
        window.onscroll = function () {
            if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
                document.getElementById("rocket").style.display = "block";
            } else {
                document.getElementById("rocket").style.display = "none";
            }
        }

        let local_uid = <?php echo User::uid(); ?>;
        let admin = <?php echo User::admin(); ?>;
        let pages = <?php echo $replies_pages; ?>;
        let curpage = 0;

        let sub_pages = new Map();
        let sub_curpage = new Map();

        function show_modal(modal) {
            $("#" + modal).modal("show");
        }
        function hide_delete_modal(modal) {
            $("#" + modal).modal("hide");
        }
        function delete_blog(id) {
            hide_delete_modal();
            $.ajax({
                url: "./api.php",
                type: 'POST',
                data: {
                    "type": "delete",
                    "id": id
                },
                async: true,
                success: function(data) {
                    if (data !== "success") {
                        alert("删除失败，" + data);
                    } else {
                        window.location.href = "/";
                    }
                },
                error:  function(XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseText);
                    alert("删除失败，未知错误");
                }
            });
        }
        function post_reply(floor, sub) {
            let btn = $("#reply-button" + (sub ? "-" + floor : ""));
            btn.addClass("disabled loading");
            let textarea_div = $(sub ? "#reply-textarea-sub" : "#reply-textarea");
            $.ajax({
                url: "./api.php",
                type: 'POST',
                data: {
                    "type": "post-reply",
                    "sub": sub,
                    "in_blog": <?php echo $id; ?>,
                    "text": textarea_div.val(),
                    "floor": floor
                },
                async: true,
                success: function(data) {
                    console.log(data);
                    try {
                        let obj = JSON.parse(data);
                        if (obj['statu'] === "success") {
                            if (!sub) sub_pages.set(floor + 1, 0);
                            let div = $(sub ? "#reply-subs-" + floor : "#blog-replies");
                            div.css("display", "block");
                            div.prepend(obj_to_str(obj['reply'], sub));
                            textarea_div.val("");
                        } else {
                            alert("评论失败");
                        }
                    } catch (e) {
                        alert("评论失败，" + data);
                    }
                    btn.removeClass("loading");
                },
                error:  function(XMLHttpRequest, textStatus, errorThrown) {
                    alert("评论失败：" + XMLHttpRequest.responseText);
                }
            });
            setTimeout(function () {
                btn.removeClass("disabled");
            }, 5000);
        }
        function get_reply(page) {
            curpage = page;
            $.ajax({
                url: "./api.php",
                type: 'POST',
                data: {
                    "type": "get-reply",
                    "in_blog": <?php echo $id; ?>,
                    "page": page
                },
                async: true,
                success: function(data) {
                    parse_replies(data);
                },
                error:  function(XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseText);
                }
            });
        }
        function get_sub_reply(page, floor) {
            sub_curpage.set(floor, page);
            $.ajax({
                url: "./api.php",
                type: 'POST',
                data: {
                    "type": "get-sub-reply",
                    "in_blog": <?php echo $id; ?>,
                    "page": page,
                    "floor": floor
                },
                async: true,
                success: function(data) {
                    let obj = JSON.parse(data);
                    let div = $("#reply-subs-" + floor);
                    div.empty();
                    div.append(obj_to_subs(obj));
                    div.append(pages_selector(page, sub_pages.get(floor), ((page, num) => '<a onclick="get_sub_reply(' + page + ',' + floor + ')" class="item">' + num + '</a>'), 'margin-top: 16px', 'sub-' + floor));
                },
                error:  function(XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseText);
                }
            });
        }
        function try_delete_reply(floor, sub, sub_floor) {
            $("#delete-reply-modal #submit").attr("onclick", "delete_reply(" + floor + "," + sub + "," + sub_floor + ")");
            show_modal("delete-reply-modal");
        }
        function delete_reply(floor, sub, sub_floor) {
            hide_delete_modal("delete-reply-modal");
            $.ajax({
                url: "./api.php",
                type: 'POST',
                data: {
                    "type": "delete-reply",
                    "in_blog": <?php echo $id; ?>,
                    "floor": floor,
                    "sub": sub,
                    "sub_floor": sub_floor
                },
                async: true,
                success: function(data) {
                    if (data !== "success") {
                        alert(data);
                    } else {
                        $("#reply-" + floor + sub + sub_floor + "-div").hide();
                    }
                },
                error:  function(XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseText);
                }
            });
        }
        function parse_replies(data) {
            let obj = JSON.parse(data);
            let div = $("#blog-replies");
            div.empty();
            for (let i = 0; i < obj.length; ++i) {
                sub_pages.set(obj[i]['floor'], parseInt((obj[i]['sub_sum'] - 1) / 5));
                sub_curpage.set(obj[i]['floor'], 0);
                div.append(
                    obj_to_str(obj[i], false)
                );
            }
            $("#page-selector").remove();
            let selector = $("#reply-page-selector");
            selector.empty();
            selector.append(pages_selector(curpage, pages, (page, num) => '<a onclick="get_reply(' + page + ')" class="item">' + num + '</a>'), "");
        }
        function obj_to_str(obj, sub) {
            let user_href = '../user/space.php?uid=' + obj['owner'];
            return '' +
            '<div class="comment" id="reply-' + obj['floor'] + obj['sub'] + obj['sub_floor'] + '-div">' +
                '<a class="avatar" href="' + user_href + '">' +
                    '<img src="https://cravatar.cn/avatar/' + obj['owner_emmd5'] + '?d=<?php echo $config['def_user_avatar'] ?>" alt="avatar">' +
                '</a>' +
                '<div class="content" ' + (sub ? '' : 'id="reply-' + obj['floor'] + '"') + '>' +
                    '<a class="author" href="' + user_href + '">' + obj['owner_nickname'] + ' ' + (obj['owner_admin'] ? '<?php echo User::get_admin_label()?>' : '') + (obj['owner'] === <?php echo $id ?> ? '<?php echo User::get_owner_label() ?>' : '') + obj['owner_title'] + '</a>' +
                    '<div class="metadata">' +
                        '<span class="date">' + obj['time'] + '</span>' +
                    '</div>' +
                    '<div class="text">' +
                        obj['text'] +
                    '</div>' +
                    '<div class="actions">' +
                        (!sub ? '<a class="reply" onclick="show_reply_form(' + obj['floor'] + ')">回复</a>' : '') +
                        (admin || local_uid === obj['owner'] ? '<a class="reply" onclick="try_delete_reply(' + obj['floor'] + ',' + obj['sub'] + ',' + obj['sub_floor'] + ')">删除</a>' : '') +
                    '</div>' +
                '</div>' +
                '<div class="comments" style="display: ' + (obj['sub_sum'] > 0 ? "block" : "none") + ';" ' + (sub ? '' : ('id="reply-subs-' + obj['floor'])) + '">' +
                    (!sub && obj['sub_sum'] > 0 ? obj_to_subs(obj['subs']) : "") +
                    (!sub && sub_pages.get(obj['floor']) > 0 ? pages_selector(0, sub_pages.get(obj['floor']), ((page, num) => '<a onclick="get_sub_reply(' + page + ',' + obj['floor'] + ')" class="item">' + num + '</a>'), 'margin-top: 16px', 'sub-' + obj['floor']) : '') +
                '</div>' +
            '</div>'
        }
        function obj_to_subs(obj) {
            let str = '';
            for (let i = 0; i < obj.length; ++i) {
                str += obj_to_str(obj[i], true);
            }
            return str;
        }
        function show_reply_form(floor) {
            $("#replier-sub-div").remove();
            $("#reply-" + floor).append(
                '<form class="ui reply form" id="replier-sub-div">' +
                    '<div class="field">' +
                        '<textarea id="reply-textarea-sub"></textarea>' +
                    '</div>' +
                    '<div class="ui primary submit labeled icon button" id="reply-button-' + floor + '" onclick="post_reply(' + floor + ', true)">' +
                        '<i class="icon edit"></i>回复' +
                    '</div>' +
                '</form>'
            );
        }
        let btn = $("#clt-btn");
        function add_collect() {
            $.ajax({
                url: "../user/api.php",
                type: 'POST',
                data: {
                    "type": "add-collect",
                    "blogid": <?php echo $id; ?>
                },
                async: true,
                success: function(data) {
                    console.log(data);
                    if (data !== "success") {
                        alert(data);
                    } else {
                        btn.removeClass();
                        btn.addClass("ui mini button");
                        btn.empty();
                        btn.append('<i class="star icon"></i>已收藏');
                        btn.attr("onclick", "dis_collect()");
                    }
                },
                error:  function(XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseText);
                }
            });
        }
        function dis_collect() {
            $.ajax({
                url: "../user/api.php",
                type: 'POST',
                data: {
                    "type": "dis-collect",
                    "blogid": <?php echo $id; ?>
                },
                async: true,
                success: function(data) {
                    console.log(data);
                    if (data !== "success") {
                        alert(data);
                    } else {
                        btn.removeClass();
                        btn.addClass("ui mini yellow button");
                        btn.empty();
                        btn.append('<i class="star icon"></i>收藏');
                        btn.attr("onclick", "add_collect()");
                    }
                },
                error:  function(XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseText);
                }
            });
        }
        get_reply(0);
        function get_copier(tgt) {
            return '<i class="copy outline icon copier copier-float" data-clipboard-target="#' + tgt + '" data-content="复制成功"></i>';
        }
        let codes = 0;
        window.onload = function () {
            setTimeout(function () {
                $('code').each(function () {
                    this.id = 'code-' + ++codes;
                    $(this).css("position", "relative");
                    let txt = $(this).html();
                    $(this).html(txt + get_copier('code-' + codes));
                });
                load_copier();
            }, 100)
        }
    </script>
<?php
$loader->page_end();