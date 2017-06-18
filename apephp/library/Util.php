<?php
// +----------------------------------------------------------------------
// | ApePHP [ a lightweight php framework ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.xiaocp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Ape <me@xiaocp.com>
// +----------------------------------------------------------------------

namespace ape;

class Util{

    /**
     * 浏览器友好的变量输出
     * @param mixed         $var 变量
     * @param boolean       $echo 是否输出 默认为true 如果为false 则返回输出字符串
     * @param string        $label 标签 默认为空
     * @param integer       $flags htmlspecialchars flags
     * @return void|string
     */
    public static function dump($var, $echo = true, $label = null, $flags = ENT_SUBSTITUTE)
    {
        $label = (null === $label) ? '' : rtrim($label) . ':';
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
        if (IS_CLI && false) {
            $output = PHP_EOL . $label . $output . PHP_EOL;
        } else {
            if (!extension_loaded('xdebug')) {
                $output = htmlspecialchars($output, $flags);
            }
            $output = '<pre>' . $label . $output . '</pre>';
        }
        if ($echo) {
            echo($output);
            return;
        } else {
            return $output;
        }
    }

    /**
     * 将一个字串中含有全角的数字字符、字母、空格或'%+-()'字符转换为相应半角字符
     * @param $str
     * @return string
     */
    public static function make_semiangle($str)
    {
        $arr = array('０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',
                     '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9',
                     'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E',
                     'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J',
                     'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O',
                     'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T',
                     'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y',
                     'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd',
                     'ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i',
                     'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n',
                     'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's',
                     'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x',
                     'ｙ' => 'y', 'ｚ' => 'z',
                     '（' => '(', '）' => ')', '［' => '[', '］' => ']', '【' => '[',
                     '】' => ']', '〖' => '[', '〗' => ']', '「' => '[', '」' => ']',
                     '『' => '[', '』' => ']', '｛' => '{', '｝' => '}', '《' => '<',
                     '》' => '>',
                     '％' => '%', '＋' => '+', '—' => '-', '－' => '-', '～' => '-',
                     '：' => ':', '。' => '.', '、' => ',', '，' => '.', '、' => '.',
                     '；' => ',', '？' => '?', '！' => '!', '…' => '-', '‖' => '|',
                     '＂' => '"', '＇' => '`', '｀' => '`', '｜' => '|', '〃' => '"',
                     '　' => ' ');

        return strtr($str, $arr);
    }

}