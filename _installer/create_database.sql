create table blogs
(
    id               int        default 0 not null
        primary key,
    owner            int                  null,
    create_time      tinytext             null,
    latest_edit_time tinytext             null,
    editors          tinytext             null,
    latest_editor    int                  null,
    md5              tinytext             null,
    title            text                 null,
    tags             text                 null,
    parti            tinytext             null,
    visible          tinyint(1) default 1 null,
    intro            text                 null,
    replies_sum      int        default 0 null,
    page_view        int        default 0 null,
    likes            int        default 0 null
);

create table broadcasts
(
    type     tinytext             null,
    link     text                 null,
    id       int auto_increment
        primary key,
    time     tinytext             null,
    stick    tinyint(1) default 0 null,
    `update` tinytext             null,
    title    text                 null
)
    auto_increment = 1;

create table collections
(
    uid  int           not null
        primary key,
    json json          null,
    cnt  int default 0 null
);

create table forgetpw
(
    id        int auto_increment
        primary key,
    password  text                 null,
    timestamp int                  null,
    code      text                 null,
    uid       int                  not null,
    checked   tinyint(1) default 0 not null,
    reason    tinytext             null,
    constraint forgetpw_id_uindex
        unique (id)
)
    auto_increment = 1;

create table imgur
(
    owner       int      null,
    md5         tinytext null,
    upload_time tinytext null,
    type        tinytext null,
    id          int auto_increment
        primary key,
    size        int      null,
    suffix      tinytext null
)
    auto_increment = 1;

create table messages
(
    `from`  int                  null,
    id      int auto_increment
        primary key,
    `to`    int                  null,
    text    text                 null,
    time    tinytext             null,
    type    tinytext             null,
    be_read tinyint(1) default 0 null,
    constraint messages_id_uindex
        unique (id)
)
    auto_increment = 1;

create table replies
(
    in_blog   int        default 0     null,
    floor     int        default 0     null,
    owner     int        default 0     null,
    sub       tinyint(1) default 0     null,
    sub_floor int        default 0     null,
    text      text collate utf8mb4_bin null,
    time      tinytext                 null,
    sub_sum   int        default 0     null,
    reply_id  int auto_increment
        primary key
)
    auto_increment = 1;

create table site
(
    blogs_sum int default 0 null
);

create table users
(
    uid           int auto_increment,
    allow_be_srch tinyint(1) default 1 null,
    email         tinytext             not null,
    nickname      tinytext             null,
    password      text                 null,
    regtime       tinytext             null,
    admin         int        default 0 null,
    verified      tinyint(1) default 0 null,
    verify_time   int        default 0 null,
    verify_code   int        default 0 null,
    intro         text                 null,
    ban           int        default 0 null,
    title         tinytext             null,
    constraint users_uid_uindex
        unique (uid)
)
    auto_increment = 1;
