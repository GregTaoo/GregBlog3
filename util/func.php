<?php
//math
function in_range($num, $min, $max): bool
{
    return $num >= $min && $num <= $max;
}
function check_length($str, $min, $max): bool
{
    return !empty($str) && in_range(strlen($str), $min, $max);
}
function is_tinytext($str): bool
{
    return check_length($str, 0, 255);
}
function is_text($str): bool
{
    return check_length($str, 0, 65535);
}

//web
function get_url_prefix(): string
{
    $config = Info::config();
    return $config['https'] > 0 ? "https://" : "http://";
}
function get_cur_url(): string
{
    return $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
}
function get_cur_url_encoded(): string
{
    $url = str_replace("?", "$", get_cur_url());
    return str_replace("=", ",", str_replace("&", "@", $url));
}
function get_full_cur_url_encoded(): string
{
    $config = Info::config();
    return get_url_prefix().$config['domain'].get_cur_url_encoded();
}
function cur_url_decode($url): string
{
    $url = str_replace("$", "?", $url);
    return str_replace(",", "=", str_replace("@", "&", $url));
}
function redirect($url)
{
    echo '<script>window.location.replace("'.$url.'");</script>';
}
function refresh($time)
{
    echo '<script>
            let time = '.$time.';
            setTimeout(function () {
                window.location.reload();
            }, time);
          </script>';
}
function js_alert($str)
{
    echo '<script>
             alert("'.$str.'");
          </script>
    ';
}
function echo_error_body($loader, $msg)
{
    echo '<body>';
    $loader->top_menu();
    echo '<div class="ui main container" style="margin-top: 64px">
        <div class="ui error message" id="error">
            '.$msg.'
        </div>
    </div>
    </body>';
}
function get_error_msg($msg): string
{
    return '
    <div class="ui error message" id="error">
    '.$msg.'
    </div>
    ';
}
function get_success_msg($msg): string
{
    return '
    <div class="ui success message" id="error">
    '.$msg.'
    </div>
    ';
}
function get_normal_msg($msg): string
{
    return '
    <div class="ui message" id="error">
    '.$msg.'
    </div>
    ';
}