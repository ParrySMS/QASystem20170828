<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-20
 * Time: 17:36
 */

/**生成token密钥
 * @param $user_id
 * @param $openid
 * @return string
 */
require "../controller/encrypt.php";//加密模块

//echo createToken(24,"93a27b0bd99bac3e68a440b48aa421ab");

function createToken($user_id, $openid)
{
    $str = $user_id . "+" . md5($openid)."+".date("Y-m-H d:i:s");
    return thinkEncrypt($str);
}

