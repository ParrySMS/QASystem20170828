<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-20
 * Time: 1:48
 */

require "../config/database_info_local.php";//数据库配置
require "../controller/json.php";//发送json模块
require "../controller/safe.php";//安全检查模块
require "../controller/decrypt.php";//token解密模块
require "../controller/user.php";
require "../model/medoo.php";//数据库框架
date_default_timezone_set('Asia/Shanghai');
$database = new medoo(array("database_name" => DATABASE_NAME));

if ($_POST == null || !is_array($_POST)) {
    JsonPrint(400, "post null", null);
} else {
    $token = isset($_POST["token"]) ? $_POST["token"] : null;
    $token = safe_check($token);
    $user_id = tokenDecrypt($token, $database);
    if ($user_id == null) {
        JsonPrint(400, "token error", null);
    } elseif (!isVaildUid($user_id, $database)) {
        JsonPrint(400, "token id error", null);
    } else {
        $is_set = userSettingIsDone($user_id, $database);
        if (is_null($is_set)) {
            JsonPrint(500, "data in DB error", null);
        } elseif ($is_set == true) {
            JsonPrint(403, "already set userinfo", null);
        } else {
            $sex = safe_check(isset($_POST["sex"]) ? $_POST["sex"] : null);
            $region = safe_check(isset($_POST["region"]) ? $_POST["region"] : null);
            $grade = safe_check(isset($_POST["grade"]) ? $_POST["grade"] : null);
            $wechat = isset($_POST["wechat"]) ? $_POST["wechat"] : null;
            $phone = safe_check(isset($_POST["phone"]) ? $_POST["phone"] : null);
            //下面这行代码慎改 务必区分empty is_null 对各自不同类型的空的不同取值
            if (is_null($sex) || $sex == "" || empty($region) || empty($grade) || empty($wechat) || empty($phone)) {
                JsonPrint(400, "param null error", null);
            } else {
                $seting = setUserInfo($user_id, $sex, $region, $grade, $wechat, $phone, $database);
                if ($seting == false) {
                    JsonPrint(500, "setting failed", null);
                } else {
                    JsonPrint(200, null, null);
                }
            }
        }
    }
}

