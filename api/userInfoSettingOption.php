<?php
/**
 * 返回前端需要的选项参数数组
 */
require "../config/database_info_local.php";//数据库配置
require "../config/option.php"; //选项配置文件
require "../controller/json.php";//发送json模块
require "../controller/safe.php";//安全检查模块
require "../controller/decrypt.php";//token解密模块
require "../controller/user.php";//对user表操作模块
require "../model/medoo.php";//数据库框架
require "../model/Option.class.php";//选项对象
date_default_timezone_set('Asia/Shanghai');
$database = new medoo(array("database_name" => DATABASE_NAME));

if ($_GET == null || !is_array($_GET)) {
    JsonPrint(400, "get null", null);
} else {
    $token =isset($_GET["token"])?$_GET["token"]:null;
    $token = safe_check($token);
    $user_id = tokenDecrypt($token, $database);
    if ($user_id == null) {
        JsonPrint(400, "token error", null);
    } elseif (!isVaildUid($user_id,$database)){
        JsonPrint(400, "token id error", null);
    }else {
        $option_region = eval(OPTION_REGION);
        $option_grade = eval(OPTION_GRADE);
        if ($option_grade == null || $option_region == null) {
            JsonPrint(500, "data null error", null);
        } elseif (!is_array($option_region) || !is_array($option_grade)) {
            JsonPrint(500, "data array error", null);
        } else {
            $retdata = new Option($option_region, $option_grade);
            JsonPrint(200, null, $retdata);
        }
    }
}