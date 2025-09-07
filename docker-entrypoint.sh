#!/bin/sh
set -e

CONFIG_FILE=/var/www/html/config/Config.json
if [ ! -f "$CONFIG_FILE" ]; then
    cat > $CONFIG_FILE <<EOF
{
  "website_name": "GregBlog",
  "https": false,
  "domain": "localhost:8080",
  "mysql_ip": "db",
  "mysql_database": "gregdb",
  "mysql_username": "greg",
  "mysql_password": "secret",
  "mailer_host": "smtp.example.com:465",
  "mailer_username": "greg",
  "mailer_address": "noreply@example.com",
  "mailer_password": "mailpw",
  "mailer_name": "GregTao",
  "use_local_cdn": true
}
EOF
fi

exec "$@"
