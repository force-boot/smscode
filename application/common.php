<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use app\common\controller\BaseController;

/**
 * 验证时间范围
 * @param $begin
 * @param $end
 * @return bool
 */
function checkTimeSlot($begin, $end)
{
    $checkDayStr = date('Y-m-d ', time());
    $timeBegin = strtotime($checkDayStr . "{$begin}" . ":00");
    $timeEnd = strtotime($checkDayStr . "{$end}" . ":00");
    $curr_time = time();
    return ($curr_time >= $timeBegin && $curr_time <= $timeEnd) ? true : false;
}

/**
 * 格式化手机号码
 * @param $phoneStr string
 * @return array
 */
function formatPhone($phoneStr)
{
    // 去除所有空格 025-87771586/
    $phoneStr = trim(trimAll($phoneStr), '/');
    // 分隔成数组
    $phoneArr = explode('/', $phoneStr);
    $phone = [];
    foreach ($phoneArr as $value) {
        if (empty($value)) continue;
        if (strlen($value) != 14) continue;
        if (!checkPhone(str_replace('+86', '', $value))) continue;

        $phone[] = $value;
    }
    return $phone;
}

/**
 * 去除字符串中所有的空格
 * @param string $str
 * @return string|string[]
 */
function trimAll(string $str)
{
    $qian = array(" ", "　", "\t", "\n", "\r");
    $hou = array("", "", "", "", "");
    return str_replace($qian, $hou, $str);
}

/**
 * 验证手机号是否合法
 * @param $number
 * @return bool
 */
function checkPhone($number): bool
{
    $checkphone = '/^1[3-9][0-9]\d{8}$/';
    return preg_match($checkphone, $number);
}

/**
 * 验证座机合法
 * @param $number
 * @return bool
 */
function checkTel($number): bool
{

    $checktel = "/^(\d{3}-)(\d{8})$|^(\d{4}-)(\d{7})$|^(\d{4}-)(\d{8})$/";
    return preg_match($checktel, $number);
}

/**
 * 统一json格式输出 多语言
 * @param $msg
 * @param int $code
 * @return \think\response\Json
 */
function jsonLangMsg($msg, $code = -1)
{
    return (new BaseController())->jsonMsg(lang($msg), $code);
}

/**
 * 统一json格式输出
 * @param $msg
 * @param int $code
 * @param $list
 * @return \think\response\Json
 */
function jsonMsg($msg, $code = -1, $list = [])
{
    return (new BaseController())->jsonMsg($msg, $code, $list);
}

/**
 * 字符串转码 utf-8
 * @param $data
 * @return false|string|string[]|null
 */
function characet($data)
{
    return mb_convert_encoding($data, 'utf-8', 'gb2312');
}

/**
 * 获取IP所在地 省-市
 * @param string $ip ip地址 为空获取当前请求IP
 * @return string
 */
function getIpCity(string $ip = '')
{
    if (empty($ip)) $ip = request()->ip();
    $url = 'http://whois.pconline.com.cn/ipJson.jsp?json=true&ip=';
    $city = file_get_contents($url . $ip);
    $city = mb_convert_encoding($city, "UTF-8", "GB2312");
    $city = json_decode($city, true);
    return $city['pro'] . '-' . $city['city'];
}

/**
 * 生成唯一key
 * @param string $param 附加参数
 * @return string
 */
function createUniqueKey(string $param = 'token'): string
{
    $md5 = md5(uniqid(md5(microtime(true)), true));
    return sha1($md5 . md5($param));
}

/**
 * 字符串转义
 * @param $string
 * @param int $force
 * @param bool $strip
 * @return array|string
 */
function daddslashes($string, $force = 0, $strip = FALSE)
{
    !defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
    if (!MAGIC_QUOTES_GPC || $force) {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = daddslashes($val, $force, $strip);
            }
        } else {
            $string = addslashes($strip ? stripslashes($string) : $string);
        }
    }
    return $string;
}

/**
 * 加密解密
 * @param $string
 * @param string $operation
 * @param string $key
 * @param int $expiry
 * @return bool|string
 */
function authcode($string, $operation = '', $key = '', $expiry = 0)
{
    $ckey_length = 4;
    $key = md5($key ? $key : 'tgyd');
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? $operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length) : '';
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ $box[($box[$a] + $box[$j]) % 256]);
    }
    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}