<?php
$step = empty($_GET['step']) ? 0 : $_GET['step'];
$jump = empty($_GET['jump']) ? 0 : $_GET['jump'];
if ($step == 0) {
    ?>
    <form method="post" action="?step=1">
        <label>网站名称<input type="text" name="name" value="GregBlog"></label><br>
        <label>
            启用https
            <select name="https">
                <option value="false" selected>不启用</option>
                <option value="true">启用</option>
            </select>
        </label><br>
        <label>域名/访问地址<input type="text" name="domain"></label><br>
        <label>MySQL连接地址<input type="text" name="mysql-ip"></label><br>
        <label>MySQL数据库名<input type="text" name="mysql-db"></label><br>
        <label>MySQL用户名<input type="text" name="mysql-name"></label><br>
        <label>MySQL密码<input type="text" name="mysql-pw"></label><br>
        <label>邮件服务器地址+端口<input type="text" name="mail-host" placeholder="xx.xx:xx"></label><br>
        <label>邮箱用户名<input type="text" name="mail-un"></label><br>
        <label>邮箱密钥<input type="text" name="mail-pw"></label><br>
        <label>邮箱地址(通常和用户名相同)<input type="text" name="mail-addr"></label><br>
        <label>邮箱发送者名称(可自定义)<input type="text" name="mail-name"></label><br>
        <input type="submit" value="开始部署"><br>
    </form>
    <?php
}
else if ($step == 1) {
    if ($jump != 1) {
        echo '请稍等片刻...<br>';
        try {
            $backup = (array) json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../server/ConfigBackup.json'), JSON_UNESCAPED_UNICODE);
            mkdir($_SERVER['DOCUMENT_ROOT'] . '/../config/');
            $config = fopen($_SERVER['DOCUMENT_ROOT'] . '/../config/Config.json', "w");
            $backup['website_name'] = $_POST['name'];
            $backup['https'] = $_POST['https'] == "true";
            $backup['domain'] = $_POST['domain'];
            $backup['mysql_password'] = $_POST['mysql-pw'];
            $backup['mysql_username'] = $_POST['mysql-name'];
            $backup['mysql_ip'] = $_POST['mysql-ip'];
            $backup['mysql_database'] = $_POST['mysql-db'];
            $backup['mailer_username'] = $_POST['mail-un'];
            $backup['mailer_host'] = $_POST['mail-host'];
            $backup['mailer_address'] = $_POST['mail-addr'];
            $backup['mailer_password'] = $_POST['mail-pw'];
            $backup['mailer_name'] = $_POST['mail-name'];
            fwrite($config, stripcslashes(json_encode($backup, JSON_UNESCAPED_UNICODE)));
            fclose($config);
        } catch (Exception $e) {
            die($e . '<br/><h2>配置文件拷贝失败，请重试</h2><br>');
        }
        echo '配置文件拷贝成功...<br>';
        try {
            $config = (array) json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../config/Config.json'));
            $conn = mysqli_connect($config['mysql_ip'], $config['mysql_username'], $config['mysql_password'], $config['mysql_database']);
            $f_sql = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../_installer/create_database.sql');
            $sentences = explode(';', $f_sql);
            foreach ($sentences as $sentence) {
                $sentence = trim($sentence);
                if (empty($sentence)) continue;
                if (!mysqli_query($conn, $sentence)) throw new Exception("数据库语句执行失败, ".mysqli_error($conn).": ".$sentence."<br>");
            }
        } catch (Exception $e) {
            die($e . '数据表创建失败<br>');
        }
        echo '数据库配置成功...请新建管理员账户:<br>';
    }
    ?>
    <form method="post" action="?step=2">
        <label>邮箱<input type="email" name="email" placeholder="xx@xx.xx"></label><br>
        <label>密码<input type="password" name="pw">无重复输入密码验证，请注意！</label><br>
        <label>用户名<input type="text" name="name"></label><br>
        <input type="submit" value="创建"><br>
    </form>
    <?php
}
else if ($step == 2) {
    $config = (array) json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../config/Config.json'));
    $conn = mysqli_connect($config['mysql_ip'], $config['mysql_username'], $config['mysql_password'], $config['mysql_database']);
    $regtime = (new DateTime()) -> format('Y-m-d H:i:s');
    $sql = "INSERT INTO users (email, nickname, password, regtime, admin, intro, title) 
            VALUES ('".$_POST['email']."', '".$_POST['name']."', '".password_hash($_POST['pw'], PASSWORD_DEFAULT)."', '".$regtime."', 1, '站长', '站长')";
    if (!mysqli_query($conn, $sql)) {
        die('新建账户失败！请<a href="?step=1&jump=1">重试</a><br>'.mysqli_error($conn));
    }
    die("恭喜您的站点配置成功<br>");
}