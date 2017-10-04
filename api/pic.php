<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-26
 * Time: 12:58
 */

require "../config/database_info_local.php";//数据库配置
require "../controller/json.php";//发送json模块
require "../controller/safe.php";//安全检查模块
require "../controller/decrypt.php";//token解密
require "../model/Pic.class.php";
require "../model/medoo.php";//数据库框架
require "../controller/user.php"; //对user表的操作模块

date_default_timezone_set('Asia/Shanghai');
$database = new medoo(array("database_name" => DATABASE_NAME));


if ($_GET == null || !is_array($_GET)) {
    JsonPrint(400, "post null", null);
} else {
    $token = isset($_GET["token"]) ? $_GET["token"] : null;
    $token = safe_check($token);
    if ($token != "qa") {
        JsonPrint(400, "token error", null);
    } else {
        $url = 'http://cosdemo-1253322052.cosgz.myqcloud.com/qa/xiangda1.jpg';
        $retdata = new Pic($url);
        JsonPrint(200, null, $retdata);
    }
}