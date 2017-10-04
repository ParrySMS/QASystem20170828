<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-24
 * Time: 14:46
 */
require "../jssdk/jssdk.php";
require "../config/wxApp.php";
require "../model/JsRetdata.class.php";
require "../controller/safe.php";
require "../controller/json.php";//发送json模块


 $url = isset($_POST["url"])?$_POST["url"]:null;
 if(is_null($url)){
     JsonPrint(400,"url null",null);
 }else{
	//$url="www.baidu.com";
    safe_check($url);
    $jssdk = new JSSDK(APPID, APPSECRET);
    $signPackage = $jssdk->getSignPackage($url);

    $timestamp = $signPackage["timestamp"];
    $nonceStr = $signPackage["nonceStr"];
	$signatrue = $signPackage["signature"];
    $retdata = new JsRetdata($timestamp,$nonceStr,$signatrue);
    JsonPrint(200,null,$retdata);
}

