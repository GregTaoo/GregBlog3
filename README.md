## 环境要求
- PHP = 7.4
- MySQL >= 5.7

## 安装
- 拷贝 github 项目到本地
```
git clone https://github.com/gregtaoo/gregblog3.git
```
- 修改 `docker-compose.yml` 中的数据库密码等，并修改 `_install/init_config.php`（后续如需修改配置，需要重新构建）
- 修改 `_install/create_database.sql` 中默认超级管理员信息（密码可从网上查询 php password_hash online 进行加密），默认密码为 adminpw
- 将 `public/static` 文件夹下的 `cdn.zip` 解压，修改权限为 755
- 构建 Docker 镜像并启动
```
docker compose up --build
```

## 注意
使用的开源项目列表:
public/static/page/credits.html

*Only Chinese(Simplified) had been supported.*