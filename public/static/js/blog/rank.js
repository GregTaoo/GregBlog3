let rcm = $("#rank");
$.ajax({
    url: "./api.php",
    type: 'POST',
    data: {
        "type": "select-blogs-by-rank",
        "amount": 10
    },
    async: true,
    success: function(data) {
        console.log(data)
        let obj = JSON.parse(data);
        for (let i = 0; i < obj.length; ++i) {
            rcm.append(obj_to_str(obj[i], i + 1));
        }
    }
});
function obj_to_str(obj, rank) {
    return '' +
        '<span class="card" style="opacity: ' + (1 - rank * 0.05) + '">' +
        '<a class="content" href="./show.php?id=' + obj['id'] + '">' +
        '<div class="header">' + rank + '. ' + obj['title'] + '</div>' +
        '<div class="meta">' +
        '<span class="right floated time">' + obj['create_time'] + '</span>' +
        '<span class="category">' + obj['tags'] + '</span>' +
        '</div>' +
        '<div class="description">' +
        '<p>' + obj['intro'] + '</p>' +
        '</div>' +
        '<span class="right floated">' +
        '<i class="eye icon"></i>' +
        obj['page_view'] +
        '</span>' +
        '<i class="comment icon"></i>' +
        obj['replies_sum'] +
        '</a>' +
        '<div class="extra content">' +
        '<i class="star icon yellow disabled"></i>' +
        obj['likes'] +
        '<a class="right floated author" href="/user/space.php?uid=' + obj['owner'] + '">' +
        obj['owner_nickname'] + ' <img class="ui avatar image" src="https://cravatar.cn/avatar/' + obj['owner_emmd5'] + '?d=https://cravatar.cn/wp-content/uploads/sites/9/2021/07/00000000000000000000000000000000.png" alt="avatar">' +
        '</a>' +
        '</div>' +
        '</span>';
}