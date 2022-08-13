<?php
include '../include.php';
$loader = new Loader("post-blog");
$loader->init("发帖");
$config = Info::config();
Loader::add_css("../static/css/menu.css");
Loader::add_css("../static/css/blog.css");
Loader::add_js("../static/js/colorpicker.js");
Loader::add_js("../static/js/editor.js");
Loader::add_js($config['codemirror_js_src']);
Loader::add_css($config['codemirror_css_src']);
Loader::add_js($config['codemirror_mode_js_src']);
Loader::add_css($config['codemirror_theme_css_src']);
$loader->init_end();
if (User::local_be_banned($loader->info->conn)) {
    notice_be_banned(User::be_banned_to());
    die;
}
$is_edit = !empty($_GET['is_edit']) && $_GET['is_edit'] == "1";
$id = $is_edit ? (empty($_GET['id']) ? 0 : $_GET['id']) : 0;
$blog = new Blog($loader->info->conn, $id, true);
if ($is_edit) $blog->get_data();
if ($is_edit && !$blog->have_permission()) {
    ?>
    <body xmlns="http://www.w3.org/1999/html">
    <?php $loader->top_menu(); ?>
    <div class="ui main container" style="margin-top: 64px">
        <div class="ui error message" id="error">
            你没有权限！
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
        <?php if ($is_edit) echo '<div class="ui message">你正在编辑模式中，点击<a href="./show.php?id='.$id.'">返回</a></div>' ?>
        <div class="ui error message" id="error"
        <?php
        echo !User::logged() ? ">你尚未登录" : (!User::verified() ? ">请先验证邮箱" : "hidden>");
        if (!User::logged()) {
            redirect("../user/login.php?from=".get_full_cur_url_encoded()."&alert=登录后才能发帖！");
            die;
        } else if (!User::verified()) {
            redirect("../user/verify.php");
            die;
        }
        ?>
        </div>
        <form class="ui form">
            <div class="ui segment">
                辅助工具：
                <a href="/imgur/upload.php" class="ui primary button">
                    <i class="file image icon"></i>
                    图床
                </a>
                <a href="https://sm.ms" class="ui button">
                    <i class="file image icon"></i>
                    SM.MS图床
                </a>
            </div>
            <div class="field required">
                <label>标题</label>
                <input type="text" id="title" placeholder="Title" <?php if ($is_edit) echo 'value="'.$blog->title.'"' ?>>
            </div>
            <div class="field">
                <label>简介</label>
                <input type="text" id="intro" placeholder="Introduction" <?php if ($is_edit) echo 'value="'.$blog->intro.'"' ?>>
            </div>
            <div class="field">
                <label>标签（逗号隔开每个标签）</label>
                <input type="text" id="tags" placeholder="Tags" <?php if ($is_edit) echo 'value="'.$blog->tags.'"' ?>>
            </div>
            <?php if (!$blog->is_editor) { ?>
            <div class="field">
                <label>协同编辑人员（逗号隔开UID）</label>
                <input type="text" id="editors" placeholder="Editors" <?php if ($is_edit) echo 'value="'.$blog->editors.'"' ?>>
            </div>
            <?php } ?>
            <div class="ui segment">
                <div class="field">
                    <div class="ui styled fluid accordion" id="accordion">
                        <div class="title" id="acc-title" style="color: dodgerblue">
                            <i class="dropdown icon"></i>
                            怎么撰写博客？
                        </div>
                        <div class="content">
                            <h4>本博客完全支持原生 <a href="https://markdown.com.cn/basic-syntax/">Markdown</a> 语法。</h4>
                            <p>在此基础上，我们支持了两种站外iframe标签：哔哩哔哩、网易云音乐。<br>使用方法：[bvideo]视频bv号[/bvideo] 以及 [music163]音乐id[/music163]</p>
                            <hr>
                            <p>为了方便编辑，支持了[br]强制换行以及[style]标签：若需跨行请使用[:style][/:style]（根据冒号多少可进行跨行嵌套）；空格用下划线替代<br>若不需跨行可以直接[style][/style]</p>
                            <p>
                                style标签目前支持的有:<br>
                                1. align(text-align): (center|left|right|c|l|r) - 使文字靠左、中、右（只支持跨行的style）。也可使用[center][/center]标签达到居中效果<br>
                                2. color: 颜色，支持十六进制以及rgb(?,?,?)格式 - 字体颜色<br>
                                3. padding(pd): (**px|**%) - 四周缩进<br>
                                4. size(font-size): **px - 调整字体大小<br>
                                5. bg(background-color): 颜色，支持十六进制以及rgb(?,?,?)格式 - 更改背景颜色<br>
                                6. bdr-r(border-radius): (**px|**%) - 更改边框圆角大小<br>
                                7. bdr-w(border-width): (**px|**%) - 更改边框宽度<br>
                                8. bdr-c(border-color): 颜色，支持十六进制以及rgb(?,?,?)格式 - 字体颜色 - 更改边框颜色<br>
                                9. bdr-s(border-style): (none|dotted|dashed|solid|double|0|1|2|3|4) - 更改边框样式（无|点状|虚线|实线|双线）<br>
                                <strong style="color: coral">例如： [style color:red;size:20px;bg:#114514;bdr-r:5px_20px]TEST TEXT[/style]</strong><br>
                            </p>
                            <p>
                                Style标签中的有关颜色类均可以直接使用以下单词：<br>
                                pink（粉）, red（红）, brown（棕）, white（白）, black（黑）, grey（灰）, gray（灰）, orange（橙）, gold（金）,
                                yellow（黄）, blue（蓝）, purple（紫）, cyan（青）, aqua（湖绿）, teal（水鸭）, green（绿）, wheat（麦色）, sliver（银）
                            </p>
                        </div>
                    </div>
                    <script>
                        let acc = $("#accordion");
                        acc.accordion({
                            selector: {
                                trigger: '#acc-title'
                            }
                        });
                    </script>
                </div>

                <div class="field">
                    <div class="ui icon buttons">
                        <div class="ui green button" onclick="editor.undo()"><i class="undo icon"></i></div>
                        <div class="ui green button" onclick="editor.redo()"><i class="redo icon"></i></div>
                    </div>

                    <div class="ui icon buttons">
                        <div class="ui green basic button" onclick="editor_bold()"><i class="bold icon"></i></div>
                        <div class="ui green basic button" onclick="editor_incline()"><i class="italic icon"></i></div>
                        <div class="ui green basic button" onclick="editor_delete_line()"><i class="strikethrough icon"></i></div>
                        <div class="ui green basic button" onclick="editor_link(false)"><i class="linkify icon"></i></div>
                        <div class="ui green basic button" onclick="editor_img()"><i class="file image icon"></i></div>
                        <div class="ui green basic button" onclick="editor_quote()"><i class="quote left icon"></i></div>
                        <div class="ui green basic button" onclick="editor_tag('center')"><i class="align center icon"></i></div>
                        <div class="ui green basic button" onclick="add_code.modal('show')"><i class="code icon"></i></div>
                        <div class="ui green basic button" onclick="picker_div.modal('show')"><i class="paint brush icon"></i></div>
                    </div>

                    <div class="ui icon buttons">
                        <div class="ui blue basic button" onclick="editor_katex(1)">公式</div>
                        <div class="ui blue basic button" onclick="editor_katex(2)">多行公式</div>
                    </div>

                    <div class="ui icon buttons">
                        <div class="ui teal basic button" onclick="editor_title(1)">H1</div>
                        <div class="ui teal basic button" onclick="editor_title(2)">H2</div>
                        <div class="ui teal basic button" onclick="editor_title(3)">H3</div>
                        <div class="ui teal basic button" onclick="editor_title(4)">H4</div>
                    </div>
                </div>
                <div class="field">
                    <textarea id="text"><?php if ($is_edit) echo $blog->origin_text ?></textarea>
                </div>
            </div>
            <?php if (!$blog->is_editor) { ?>
            <div class="ui segment">
                <div class="field">
                    <div class="ui toggle checkbox">
                        <input type="checkbox" id="visible" <?php if ($is_edit && !$blog->visible) echo 'checked="checked"' ?>>
                        <label>对外不可见</label>
                    </div>
                </div>
            </div>
            <?php } ?>
        </form>
        <button class="ui primary large button" style="margin-top: 20px" id="submit">发帖</button>
    </div>
    <?php $loader->footer(); ?>
    <div class="ui mini modal" id="pick-color">
        <i class="close icon"></i>
        <div class="header">
            颜色选择器
        </div>
        <div class="content">
            <label>
                <input type="color" id="color-picker">
                <span id="picker-rbg" style="font-size: large"></span>
            </label>
            <script>
                let cp = $("#color-picker");
                cp.val('#000000');
                let cptxt = $("#picker-rbg");
                cp.on('input', function () {
                    cptxt.empty();
                    cptxt.append(cp.val());
                });
            </script>
        </div>
        <div class="actions">
            <span class="ui red button" onclick="picker_div.modal('hide')">取消</span>
            <span class="ui blue button" onclick="editor_color(cp.val(), false); picker_div.modal('hide')">仅颜色</span>
            <span class="ui green button" onclick="editor_color(cp.val(), true); picker_div.modal('hide')">插入</span>
        </div>
    </div>
    <div class="ui long modal loading" id="add-img">
        <i class="close icon"></i>
        <div class="header">插入图片
            <div class="ui orange button" id="imgur-btn" onclick="imgur_div.addClass('loading'); imgur_div.css('display', 'block'); imgur.attr('src', '/imgur/upload.php?iframe=1'); $('#imgur-btn').hide()">
                <i class="file image icon"></i>加载图床
            </div>
        </div>
        <div class="scrolling content ui segment" id="imgur-div" style="display: none">
            <iframe id="imgur" style="border: none; width: 100%; overflow-y: hidden" scrolling="no" onload="imgur_div.removeClass('loading')"></iframe>
        </div>
        <div class="actions">
            <div class="ui input">
                <input type="text" id="img-link" placeholder="图片链接" style="min-width: 260px">&nbsp;
                <input type="text" id="img-txt" placeholder="文本">
            </div>
            <div class="ui red button" onclick="add_img.modal('hide')">取消</div>
            <div class="ui green button" onclick="editor_img_insert($('#img-link').val(), $('#img-txt').val()); add_img.modal('hide')">插入</div>
        </div>
    </div>
    <div class="ui small modal" id="add-lnk">
        <i class="close icon"></i>
        <div class="header">
            添加链接
        </div>
        <div class="actions">
            <div class="ui input">
                <input type="text" id="lnk-link" placeholder="跳转链接" style="min-width: 260px">&nbsp;
                <input type="text" id="lnk-txt" placeholder="文本">
            </div>
            <div class="ui red button" onclick="add_lnk.modal('hide')">取消</div>
            <div class="ui green button" onclick="editor_link_insert($('#lnk-link').val(), $('#lnk-txt').val()); add_lnk.modal('hide')">插入</div>
        </div>
    </div>
    <div class="ui small modal" id="add-code">
        <i class="close icon"></i>
        <div class="header">
            添加代码块
        </div>
        <div class="content">
            选择语言（可插入后将语言名加在第一个```后方）
            <select class="ui search dropdown" id="code-picker">
                <option value="">无</option>
                <option value="cpp">C++</option>
                <option value="c">C</option>
                <option value="java">Java</option>
                <option value="py">Python</option>
                <option value="cs">C#</option>
                <option value="md">Markdown</option>
                <option value="html">HTML</option>
                <option value="css">CSS</option>
                <option value="php">PHP</option>
                <option value="js">JavaScript</option>
                <option value="vb">VB.Net</option>
                <option value="vbs">VBscript</option>
                <option value="kt">Kotlin</option>
                <option value="tex">LaTeX</option>
                <option value="json">Json</option>
                <option value="rs">Rust</option>
                <option value="sql">SQL</option>
                <option value="shell">Shell</option>
                <option value="swift">Swift</option>
                <option value="yml">YAML</option>
                <option value="scala">Scala</option>
                <option value="rb">Ruby</option>
                <option value="pl">Perl</option>
                <option value="nginx">Nginx</option>
                <option value="gradle">Gradle</option>
                <option value="dos">DOS</option>
                <option value="dart">Dart</option>
            </select>
        </div>
        <div class="actions">
            <div class="ui red button" onclick="add_code.modal('hide')">取消</div>
            <div class="ui green button" onclick="editor_code(code_pkr.val()); add_code.modal('hide')">插入</div>
        </div>
    </div>
    </body>
    <script>
        let editor = CodeMirror.fromTextArea(document.getElementById("text"),{
            lineNumbers: true,//显示行号
            mode: "text/markdown",  // 模式，这里指定html
            theme: "<?php echo $config['codemirror_theme']; ?>"
        });

        let picker_div = $("#pick-color");
        let imgur = $("#imgur");
        let imgur_div = $("#imgur-div");
        let add_img = $("#add-img");
        let add_lnk = $("#add-lnk");
        let add_code = $("#add-code");
        let code_pkr = $('#code-picker');
        code_pkr.dropdown();

        function show_error(error) {
            $("#error").text(error).show();
        }
        function success(data) {
            window.location.href = "./show.php?id=" + data.substring(7, data.length);
        }
        function submit() {
            $("#submit").addClass("loading");
            editor.save();
            $.ajax({
                url: "./api.php",
                type: 'POST',
                data: {
                    <?php if ($is_edit) echo '"id": '.$id.","; ?>
                    "type": "<?php echo $is_edit ? "edit" : "create" ?>",
                    "editors": $("#editors").val(),
                    "title": $("#title").val(),
                    "visible": $("#visible").prop("checked") ? "不可见" : "可见",
                    "text": $('<div>').text($("#text").val()).html(),
                    "intro": $("#intro").val(),
                    "tags": $("#tags").val()
                },
                async: true,
                success: function(data) {
                    if (data[0] !== "s") {
                        show_error(data);
                    } else {
                        success(data);
                    }
                    $("#submit").text("发帖").removeClass("loading");
                },
                error:  function(XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseText);
                    show_error("未知错误");
                    $("#submit").text("发帖");
                }
            });
        }
        $(document).ready(function() {
            $("#submit").click(function() {
                submit();
            });
        });
    </script>
<?php
$loader->page_end();