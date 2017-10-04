<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-20
 * Time: 17:39
 */
require "../config/wxApp.php";
/**
 * 以code换取openid
 * @param string $code 客户端访问得到的code
 * @param string $appid 微信公众号appid
 * @param string $appsecret 微信公众号appsecret
 * @return string openid 微信用户的openid
 * @author Parry < yh@szer.me >
 */
function getOpenidByCode($code, $appid = APPID, $appsecret = APPSECRET)
{
    //appid 和 appsecret在配置文件中
    //根据code获得Access Token 与 openid
    $access_token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
    $access_token_json = https_request($access_token_url);
    $access_token_array = json_decode($access_token_json, true);
    //var_dump($access_token_array);
    //$access_token = $access_token_array['access_token'];
     return isset($access_token_array['openid'])?$access_token_array['openid']:null;
}

function https_request($url, $data = null){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}