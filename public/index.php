<?php
include './include.php';
$loader = new Loader("index");
$loader -> init("主页");
Loader::add_css("../static/css/menu.css");
$loader -> init_end();
$config = Info::config();
?>
<body>
<?php $loader -> top_menu(); ?>
<div class="ui main container" style="margin-top: 64px">
    <div class="ui middle aligned two column centered grid" style="height: 300px; display: none" id="logo-tab">
        <div class="five wide column">
            <img alt="icon" src="favicon.png">
        </div>
        <div class="column">
            <h1>Freely speaking throughout the world!</h1>
            <p style="color: grey">Owned by GregTao</p>
            <div>
                <span class="ui search">
                    <input type="text" class="prompt" id="keyw" placeholder="Your favourite" style="width: 400px; max-width: 100%">
                </span>
                <span class="ui green button" onclick="search()" id="btn" style="width: 125px; border-radius: 100px;"><i class="search icon"></i>检索</span>
            </div>
            <script>
                let keyw = $("#keyw");
                keyw.keydown(function (e){
                    if (e.keyCode === 13) search();
                });
                function search() {
                    window.open("./search/?keyw=" + keyw.val());
                }
            </script>
        </div>
    </div>
    <div class="ui segment" id="bcs-div" hidden>
        <div class="ui header">
            公告
        </div>
        <table class="ui celled table">
            <tbody id="bcs">
            </tbody>
        </table>
    </div>
    <div class="ui segment" style="margin-top: 20px">
        <div>
            <span class="ui header">
                随机推荐（主页施工中）
            </span>
            <a class="ui orange button" href="./blog/rank.php"><i class="chess queen icon"></i>排行榜</a>
            <div class="ui privacy button" onclick="recommend()" id="refresh-recmd"><i class="sync alternate icon"></i>刷新推荐</div>
        </div>
        <div class="ui divider"></div>
        <div class="ui link four doubling cards" id="recommend">
        </div>
    </div>
</div>
<?php $loader->footer(); ?>
</body>
<script>
    let rcm = $("#recommend");
    let r_rcm = $("#refresh-recmd");
    let lgtb = $("#logo-tab");
    function recommend() {
        r_rcm.addClass("loading disabled");
        $.ajax({
            url: "./blog/api.php",
            type: 'POST',
            data: {
                "type": "randomly-select-blogs",
                "amount": 4
            },
            async: true,
            success: function(data) {
                let obj = JSON.parse(data);
                rcm.empty();
                for (let i = 0; i < obj.length; ++i) {
                    rcm.append(blog_card_obj_to_str(obj[i], '<?php echo $config['def_user_avatar'] ?>'));
                }
                r_rcm.removeClass("loading");
                setTimeout(function () {
                    r_rcm.removeClass("disabled");
                }, 2000);
            }
        });
    }
    let bcs = $("#bcs");
    function get_bcs() {
        $.ajax({
            url: "./blog/api.php",
            type: 'POST',
            async: true,
            data: {
                'type': 'get-boardcasts-list'
            },
            success: function(data) {
                console.log(data);
                let obj = JSON.parse(data);
                if (obj['list'].length > 0) $("#bcs-div").show();
                for (let i = 0; i < obj['list'].length; ++i) {
                    bcs.append(bc_obj_to_str(obj['list'][i]));
                }
            },
            error:  function(XMLHttpRequest, textStatus, errorThrown) {
                alert(XMLHttpRequest.responseText);
            }
        });
    }
    function bc_obj_to_str(obj) {
        let txt = obj['link'] === "/";
        return '' +
            '<tr id="bc' + obj['id'] + '">' +
            '<td>' + (txt ? '' : '<a href="' + obj['link'] + '">') + (obj['stick'] ? '<div class="ui mini red horizontal label">置顶</div>' : '') + obj['title'] + '<div class="ui mini purple horizontal label" style="float: right">' + obj['type'] + '</div>' + (txt ? '' : '</a>') + '</td>' +
            '<td>' + obj['update'] + '</td>' +
            '</tr>';
    }
    recommend();
    get_bcs();
    lgtb.transition('scale');
</script>
<?php
$loader -> page_end();