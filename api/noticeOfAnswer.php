<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-26
 * Time: 1:17
 */
require "../config/database_info_local.php";//数据库配置
require "../controller/json.php";//发送json模块
require "../controller/safe.php";//安全检查模块
require "../controller/decrypt.php";//token解密
require "../controller/question.php"; //对question表的操作模块
require "../controller/answer.php"; //对answer表的操作模块
require "../controller/user.php"; //对user表的操作模块
require "../model/medoo.php";//数据库框架
require "../model/Notice.class.php";//信息类文件
date_default_timezone_set('Asia/Shanghai');
$database = new medoo(array("database_name" => DATABASE_NAME));

if ($_GET == null || !is_array($_GET)) {
    JsonPrint(400, "get null", null);
} else {
    $token = isset($_GET["token"]) ? $_GET["token"] : null;
    $token = safe_check($token);
    $user_id = tokenDecrypt($token, $database);
    if ($user_id == null) {
        JsonPrint(400, "token error", null);
    } elseif (!isVaildUid($user_id, $database)) {
        JsonPrint(400, "token id error", null);
    } else {
        $myNum =getMyBestAnswerNum($user_id,$database);
        if(is_null($myNum)||!is_numeric($myNum)){
            JsonPrint(500,"get my best answer num error",null);
        }else{
            $notice_num = noticeOfMyAnswer($user_id,$myNum,$database);
            if(is_null($notice_num)){
                JsonPrint(500,"notice error",null);
            }else{
                $retdata = new Notice($notice_num);
                JsonPrint(200, null, $retdata);
            }

        }


    }
}