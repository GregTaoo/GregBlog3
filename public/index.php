<?php
include './include.php';
$loader = new Loader("index");
$loader->init("主页");
Loader::add_css("../static/css/menu.css");
$loader->init_end();
$config = Info::config();
$postcards = Loader::get_postcards($config);
?>
<body>
<?php $loader->top_menu(); ?>
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
    <table class="ui very basic center aligned table">
        <tr class="column">
            <td id="hitokoto_text">:D Loading...</td>
            <script>
                var xhr = new XMLHttpRequest();
                xhr.open('get', 'https://v1.hitokoto.cn');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        var data = JSON.parse(xhr.responseText);
                        var hitokoto = document.getElementById('hitokoto_text');
                        hitokoto.innerText = data.hitokoto + " —— " + data.creator;
                    }
                }
                xhr.send();
            </script>
        </tr>
        <tr class="column">
            <td id="jinrishici-sentence">:P Loading...</td>
            <script src="https://sdk.jinrishici.com/v2/browser/jinrishici.js" charset="utf-8"></script>
        </tr>
    </table>
    <div class="ui container" id="bcs-div" hidden>
        <div class="ui header">
            公告
        </div>
        <table class="ui celled table">
            <tbody id="bcs">
            </tbody>
        </table>
    </div>
    <div class="ui grid" style="margin-top: 20px; ">
        <div class="eight wide column">
            <div class="ui link two doubling cards" id="recommend">
            </div>
            <div style="margin-top: 20px">
                <span class="ui header">
                    <a class="ui orange button" href="./blog/rank.php"><i class="chess queen icon"></i>排行榜</a>
                    <span class="ui privacy button" onclick="recommend()" id="refresh-recmd"><i class="sync alternate icon"></i>刷新推荐</span>
                </span>
            </div>
        </div>
        <div class="two wide column">
        </div>
        <div class="six wide column">
            <div class="ui shape" id="postcards">
                <div class="sides">
                    <div class="active side">
                        <img src="<?php echo $postcards[0] ?>" width="100%" style="border-radius: 10px" alt="1">
                    </div>
                    <?php
                        for ($i = 1; $i < count($postcards); ++$i) {
                            ?>
                            <div class="side">
                                <img src="<?php echo $postcards[$i] ?>" width="100%" style="border-radius: 10px" alt="1">
                            </div>
                            <?php
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $loader->footer(); ?>
</body>
<script>
    let avatar = <?php echo "'".$config['def_user_avatar']."'" ?>;
    let postcards = $("#postcards");
    postcards.shape();
    function flips() {
        postcards.shape("flip right");
    }
    let timer = setInterval(flips, 10000);
    document.addEventListener("visibilitychange", function () {
        if (document.visibilityState === "hidden") {
            clearInterval(timer);
        } else {
            timer = setInterval(flips, 10000);
        }
    }, false);
</script>
<script src="static/js/homepage.js">
</script>
<?php
$loader->page_end();