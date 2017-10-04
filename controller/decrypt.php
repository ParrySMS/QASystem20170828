<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-19
 * Time: 1:46
 */

require "../config/key.php";

//echo tokenDecrypt("MDAwMDAwMDAwMK6bppu8uX-dfriNaIy3nZ2xurmkvLV6zn56epm1d7adrqx3rbKpg5uJzo6cf8p2bw");

/**
 * token解密方法
 * @param  string $token 要解密的token （必须是think_encrypt方法加密混淆的字符串）
 * @param object $database 数据库
 * @return int $user 解密出来的user_id
 * @author Parry < yh@szer.me >
 */
function tokenDecrypt($token, $database)
{
    if ($token == null) {
        return null;
    }else {
        $str = thinkDecrypt($token);
        $user_id = strtok($str, "+");
        $md5_openid = strtok("+");
        $openid_DB = getOpenidByUserid($user_id, $database);
        if (is_null($openid_DB)) {
            return null;
        } elseif (md5($openid_DB) != $md5_openid) {
            return null;
        } else {
            return $user_id;
        }
    }
}

/**
 * 以user_id获取库里的openid
 * @param  string $user_id 用户id
 * @param object $database 数据库
 * @return string $openid 微信的openid
 * @author Parry < yh@szer.me >
 */
function getOpenidByUserid($user_id, $database)
{
    $table = "qa_user";
    $data = $database->select($table, [
        "openid"
    ], [
        "AND" => [
            "id" => $user_id,
            "visible" => 1
        ]
    ]);
    if ($data == null||!is_array($data)) {
        return null;
    } else {
        foreach ($data as $d) {
            $openid = $d["openid"];
            return $openid;
        }
    }
}


/**
 * 系统解密方法
 * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param  string $key 加密密钥
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function thinkDecrypt($data, $key = '')
{
    $key = md5(empty($key) ? DATA_AUTH_KEY : $key);
    $data = str_replace(array('-', '_'), array('+', '/'), $data);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    $data = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data = substr($data, 10);

    if ($expire > 0 && $expire < time()) {
        return '';
    }
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = $str = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }

    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

