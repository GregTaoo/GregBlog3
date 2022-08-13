<?php
include '../include.php';
$uid = empty($_GET['uid']) ? 0 : $_GET['uid'];
$loader = new Loader("space");
$user = User::get_user($loader->info->conn, $uid, false);
$loader->init($user->exist ? $user->nickname."的空间" : "该用户不存在");
Loader::add_css("../static/css/menu.css");
$loader->init_end();
if (!$user->exist) {
    ?>
    <body>
    <?php $loader->top_menu(); ?>
    <div class="ui main container" style="margin-top: 64px">
        <div class="ui error message" id="error">
            该用户不存在！
        </div>
    </div>
    </body>
    <?php
    die;
}
?>
    <body>
    <?php $loader->top_menu(); ?>
    <div class="ui main container" style="margin-top: 64px">
        <div class="ui horizontal segments" style="overflow: hidden">
            <div class="ui segment">
                <h2 class="ui header" style="text-align: center">
                    <?php echo $user->get_avatar_img(); ?>
                    <div class="sub header">
                        <?php echo $user->nickname.($user->admin ? User::get_admin_label() : "").User::get_title_label($loader->info->conn, $user); ?>
                        <br>
                        UID: <?php echo $user->uid ?><br>
                        <a href="mailto:<?php echo $user->email; ?>"><?php echo $user->email; ?></a>
                    </div>
                </h2>
            </div>
            <div class="ui segment">
                <?php if ($user->be_banned()) echo '<strong style="color:red">该用户封禁中</strong><br>' ?>
                个性签名：<?php echo $user->intro; ?>
            </div>
        </div>
        <div class="ui segment">
            <table class="ui celled table">
                <thead>
                    <tr>
                        <th>编号</th>
                        <th>标题</th>
                        <th>发布时间</th>
                        <th>最后编辑</th>
                    </tr>
                </thead>
                <tbody id="blog-list">
                </tbody>
            </table>
            <div id="page-selector"></div>
        </div>
    </div>
    <script>
        let curpage = 0;
        let pages = 0;
        function get_blog_list(page) {
            $.ajax({
                url: "./api.php",
                type: 'POST',
                data: {
                    "type": "get-blog-list",
                    "page": page,
                    "uid": <?php echo $uid; ?>
                },
                async: true,
                success: function(data) {
                    parse_blog_list(data);
                },
                error:  function(XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseText);
                }
            });
        }
        function parse_blog_list(data) {
            let obj = JSON.parse(data);
            pages = parseInt((obj['cnt'] - 1) / 20);
            let div = $("#blog-list");
            div.empty();
            for (let i = 0; i < obj['list'].length; ++i) {
                div.append(obj_to_str(obj['list'][i]));
            }
            let ps = $("#page-selector");
            ps.empty();
            ps.append(pages_selector(curpage, pages, (page, num) => '<a onclick="get_blog_list(' + page + ')" class="item">' + num + '</a>'));
        }
        function obj_to_str(obj) {
            return '' +
                '<tr>' +
                '<td>' + obj['id'] + '</td>' +
                '<td>' +
                '<a href="../blog/show.php?id=' + obj['id'] + '">' +
                obj['title'] +
                '</a>' +
                ' <i class="eye icon"></i> ' + obj['page_view'] +
                '</td>' +
                '<td>' + obj['create_time'] + '</td>' +
                '<td>' + obj['latest_edit_time'] + ' By <a href="./space.php?uid=' + obj['latest_editor'] + '">' + obj['latest_editor_nickname'] + '</a></td>' +
                '</tr>'
        }
        get_blog_list(0);
    </script>
    <?php $loader->footer(); ?>
    </body>
<?php
$loader->page_end();