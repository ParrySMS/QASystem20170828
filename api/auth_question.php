<?php
/**以token鉴定是否24小时内未发布过问题（提问权限）
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-19
 * Time: 2:51
 */
require "../config/database_info_local.php";//数据库配置
require "../controller/json.php";//发送json模块
require "../controller/decrypt.php";//token解密
require "../controller/safe.php";//安全检查模块
require "../controller/question.php"; //对question表的操作模块
require "../controller/answer.php"; //对answer表的操作模块
require "../controller/user.php"; //对user表的操作模块
require "../model/medoo.php";//数据库框架
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

        //用户信息检查
        $status = UserSettingIsDone($user_id, $database);
        if (is_null($status)) {
            JsonPrint(500, "token vaild but user error", null);
        } elseif ($status == false) {
            JsonPrint(401, "need finish UserSetting", null);
        } else {

            //24小时检查
            $limit = hasQuestionSubmitLimit($user_id, $database);
            if (is_null($limit)) {
                JsonPrint(500, "data in DB error", null);
            } elseif ($limit == true) {
                JsonPrint(403, "already submit question today", null);
            } else {
                JsonPrint(200, null, null);
            }
        }
    }
}

