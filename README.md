# GregBlog-v3.1

See this page https://github.com/GregTaoo/GregBlog-v3.1/blob/master/public/static/page/credits.html first!

## 安装事项
0. 配置网页根目录在public文件夹下
1. 在文档根目录下server文件夹内的Info.php里填写相关信息。
2. 创建空数据库，并运行_installer文件夹内的create_database.sql的Mysql脚本。
3. 手动在users表内新建行（请填写完整，否则会出错），密码可默认为5a2e54ee57e5b7273b9a8fed78c1ebd8（PHP使用md5简单哈希的"123456test"密码，在实际用户操作中不会使用此类简单加密）或者你自定义的任何经过md5加密的密码，并进入网站登录，登录后可立即更改密码。是为初始管理员账号。
4. 进入管理员面板，更改相关配置（如邮件系统）

*Only Chinese(Simplified) has been supported.*
