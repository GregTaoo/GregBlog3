let subm = $("#submit");
let swch = $("#switch");
let refr = $("#refresh");
let s2def = $("#s2def");
let labels = $("#labels");
let json = "";
let mod = 0;
let key_list = [];
function switch_mod() {
    mod = mod === 0 ? 1 : 0;
    get_json();
}
function create_labels(obj) {
    labels.empty();
    if (mod === 1) {
        labels.append('<textarea rows="30" id="label-textarea">' + JSON.stringify(json, null, 4) + '</textarea>');
    } else {
        let str = '<table class="ui celled table">';
        for (let key in obj) {
            str += '<tr><td>' + key + '</td><td><input type="text" value="' + obj[key] + '" id="label-' + key + '"></td></tr>';
            key_list.push(key);
        }
        str += '</table>';
        labels.append(str);
    }
}
function get_json() {
    refr.addClass("loading disabled");
    $.ajax({
        url: "./api.php",
        type: 'POST',
        data: {
            'manage': 'get-config-array'
        },
        async: true,
        success: function(data) {
            console.log(data);
            json = JSON.parse(data);
            create_labels(json);
            refr.removeClass("loading");
            setTimeout(function () {
                refr.removeClass("disabled")
            }, 2000);
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}
function parse_labels() {
    if (mod === 1) {
        return $("#label-textarea").val();
    } else {
        let obj = {};
        key_list.forEach(
            (key) => {
                obj[key] = $("#label-" + key).val();
                //obj += '"' + key + '":"' + $("#label-" + key).val() + '",';
            }
        );
        return JSON.stringify(obj);
    }
}
function update_json() {
    subm.addClass("loading disabled");
    console.log(parse_labels())
    $.ajax({
        url: "./api.php",
        type: 'POST',
        data: {
            'manage': 'update-config-array',
            'json': parse_labels()
        },
        async: true,
        success: function(data) {
            if (data === "success") {
                show(false, "成功更新了内容");
                get_json();
            } else {
                show(true, data);
            }
            subm.removeClass("loading");
            setTimeout(function () {
                subm.removeClass("disabled")
            }, 2000);
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}
function set_to_def() {
    s2def.addClass("loading disabled");
    $.ajax({
        url: "./api.php",
        type: 'POST',
        data: {
            'manage': 'get-config-backup'
        },
        async: true,
        success: function(data) {
            console.log(data);
            json = JSON.parse(data);
            create_labels(json)
            s2def.removeClass("loading");
            setTimeout(function () {
                s2def.removeClass("disabled")
            }, 2000);
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}
get_json();