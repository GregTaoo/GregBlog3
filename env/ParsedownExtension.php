<?php
class GregBlogParser extends Parsedown
{
    public static array $allowed_styles = array(
        'text-align' => '/(center|left|right)/',
        'color' => '/(#[a-zA-Z\d]{6}|rgba?\(\d+,\d+,\d+(,[\d.]+)?\))/',
        'padding' => '/(\d{0,4}px|\d{1,3}%){1,4}/',
        'font-size' => '/(\d{0,3}px)/',
        'background-color' => '/(#[a-zA-Z\d]{6}|rgba?\(\d+,\d+,\d+(,[\d.]+)?\))/',
        'border-radius' => '/(\d{0,4}px|\d{1,3}%){1,4}/',
        'border-color' => '/(#[a-zA-Z\d]{6}|rgba?\(\d+,\d+,\d+(,[\d.]+)?\)){1,4}/',
        'border-width' => '/(\d{0,4}px|\d{1,3}%){1,4}/',
        'border-style' => '/(none|dotted|dashed|solid|double){1,4}/'
    );
    public static array $other_str = array(
        'align' => 'text-align',
        'pd' => 'padding',
        'size' => 'font-size',
        'bg' => 'background-color',
        'bdr-r' => 'border-radius',
        'bdr-w' => 'border-width',
        'bdr-c' => 'border-color',
        'bdr-s' => 'border-style',
    );
    public array $other_val = array(
        'text-align' => array(
            'c' => 'center',
            'l' => 'left',
            'r' => 'right'
        ),
        'border-style' => array(
            '0' => 'none',
            '1' => 'dotted',
            '2' => 'dashed',
            '3' => 'solid',
            '4' => 'double'
        )
    );
    static array $color_other_val = array(
        'pink' => '#ffc0cb',
        'red' => '#ff0000',
        'brown' => '#a52a2a',
        'white' => '#ffffff',
        'black' => '#000000',
        'grey' => '#808080',
        'gray' => '#808080',
        'orange' => '#ffa500',
        'gold' => '#ffd700',
        'yellow' => '#ffff00',
        'blue' => '#0000ff',
        'purple' => '#800080',
        'cyan' => '#0000ff',
        'aqua' => '#d4f2e7',
        'teal' => '#008080',
        'green' => '#008000',
        'wheat' => '#f5deb3',
        'sliver' => '#c0c0c0'
    );

    public static function get_other_str($str): string
    {
        $other = self::$other_str[$str];
        return empty($other) ? $str : $other;
    }
    public function get_other_val($key, $str): string
    {
        $other = $this->other_val[$key][$str];
        return empty($other) ? $str : $other;
    }

    function __construct()
    {
        $this->BlockTypes['['][] = 'Styles';
        $this->InlineTypes['['][]= 'Styles';
        $this->inlineMarkerList .= '[';

        $this->other_val['color'] = self::$color_other_val;
        $this->other_val['background-color'] = self::$color_other_val;
        $this->other_val['border-color'] = self::$color_other_val;
    }

    protected function inlineStyles($excerpt)
    {
        if (preg_match('/\[style ((?:[\w-]+:[\w%#,.()]+;?)*)](.*)\[\/style]/', $excerpt['text'], $matches))
        {
            $styles = explode(';', $matches[1]);
            $style = $this->deal_styles($styles);
            return array(

                // How many characters to advance the Parsedown's
                // cursor after being done processing this tag.
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'span',
                    'text' => $matches[2],
                    'attributes' => array(
                        'style' => $style
                    ),
                ),

            );
        } else if (preg_match('/\[bvideo]((?:bv|BV|Bv|bV)[\dA-Za-z]+)\[\/bvideo]/', $excerpt['text'], $matches)) {
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'iframe',
                    'text' => '',
                    'attributes' => array(
                        'src' => '//player.bilibili.com/player.html?bvid='.$matches[1],
                        'allowfullscreen' => 'true',
                        'border' => '0',
                        'class' => 'bvideo',
                        'frameborder' => 'no'
                    ),
                ),
            );
        } else if (preg_match('/\[music163](\d+)\[\/music163]/', $excerpt['text'], $matches)) {
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'iframe',
                    'text' => '',
                    'attributes' => array(
                        'src' => '//music.163.com/outchain/player?type=2&id='.$matches[1].'&auto=1&height=66',
                        'allowfullscreen' => 'true',
                        'border' => '0',
                        'class' => 'music163',
                        'frameborder' => 'no'
                    ),
                ),
            );
        } else if (preg_match('/\[br]/', $excerpt['text'], $matches)) {
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => '/br'
                ),
            );
        }
    }

    protected function blockStyles($line, $block)
    {
        if (preg_match('/\[(:{1,6})style ((?:[\w-]+:[\w%#,.()]+;?)*)]/', $line['text'], $matches))
        {
            $lvl = strlen($matches[1]);
            $styles = explode(';', $matches[2]);
            $style_str = $this->deal_styles($styles);
            return array(
                'char' => $line['text'][0],
                'lvl' => $lvl,
                'element' => array(
                    'name' => 'div',
                    'text' => '',
                    'attributes' => array(
                        'style' => $style_str,
                    ),
                ),
            );
        } else if (preg_match('/\[center](.*)/', $line['text'], $matches)) {
            return array(
                'char' => $line['text'][0],
                'lvl' => 0,
                'element' => array(
                    'name' => 'div',
                    'text' => '',
                    'attributes' => array(
                        'style' => 'text-align: center;',
                    ),
                ),
            );
        }
    }

    protected function blockStylesContinue($line, $block)
    {
        if (isset($block['complete']) || !isset($block['lvl']))
        {
            return;
        }

        $lvl = $block['lvl'];

        // A blank newline has occurred.
        if (isset($block['interrupted']))
        {
            $block['element']['text'] .= "\n";
            unset($block['interrupted']);
        }

        if (($lvl <= 0 && preg_match('/\[\/center]/', $line['text'])) || ($lvl > 0 && preg_match('/\[\/'.(str_repeat(':', $lvl)).'style]/', $line['text'])))
        {
            $block['element']['text'] = substr($block['element']['text'], 1);
            // This will flag the block as 'complete':
            // 1. The 'continue' function will not be called again.
            // 2. The 'complete' function will be called instead.
            $block['complete'] = true;
            return $block;
        }

        $block['element']['text'] .= "\n" . $line['body'];
        $block['element']['nonEscape'] = true;

        return $block;
    }

    protected function blockStylesComplete($block)
    {
        return $block;
    }

    protected function deal_styles($styles): string
    {
        $style_str = '';
        foreach ($styles as $style) {
            $arr = explode(':', $style);
            $key = self::get_other_str($arr[0]);
            if (!array_key_exists($key, self::$allowed_styles)) continue;
            $preg = self::$allowed_styles[$key];
            $val = str_replace('_', ' ', $this->get_other_val($key, $arr[1]));
            if (preg_match($preg, $val)) {
                $style_str = $style_str . $key . ': ' . $val . '; ';
            }
        }
        return $style_str;
    }


}