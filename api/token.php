<?php
/**
 * Created by PhpStorm.
 * 服务端发送code到此接口
 * 以code请求获取openid
 * 以openid判读用户 从DB获得user_id
 * 将user_id和openid加密  获取一个token
 * 返回token
 * author: Parry
 * Date: 2017-8-18
 * Time: 0:39
 *  yh@szer.me
 */
require "../config/database_info_local.php";//数据库配置
require "../controller/json.php";//发送json模块
require "../controller/safe.php";//安全检查模块
require "../controller/token.php"; //token操作类
require "../controller/openid.php"; //操作类
require "../controller/log.php"; //log类
require "../controller/user.php";//user表操作模块
require "../model/medoo.php";//数据库框架
require "../model/Token.class.php";//接口数据类

date_default_timezone_set('Asia/Shanghai');
$database = new medoo(array("database_name" => DATABASE_NAME));
unset($code);
$code = null;

if ($_POST == null || !is_array( $_POST)) {
    JsonPrint(400, "get null", null);
    tokenApiLog(null, 400,"get null", $database);
} else {
    $code = isset($_POST["code"])?$_POST["code"]:null;
    $code = safe_check($code);

//for ($code = 10000; $code < 10030; $code++) { //数据测试临时使用
    if (is_null($code)||$code==""||$code=="undefined") {
        JsonPrint(400, "code null", null);
        tokenApiLog(null, 400, "code null", $database);

    } else {
        if (APPID == null || APPSECRET == null) {
            $openid = md5($code);//临时测试方法
        } else {
//            echo "get openid by code";

            $openid = getOpenidByCode($code);
            //echo $code;
            //$openid ="oIhIc0lyFqLh3cpfa2xqTgiKvgmg";
        }
        if ($openid == null) {
            JsonPrint(500, "openid null", null);
            tokenApiLog($code, 500, "openid null", $database);

        } else {
//            echo $openid;
            $user_id = getUseridByOpenid($openid, $database);
            if (is_null($user_id)) {
                JsonPrint(500, "user_id null", null);
                tokenApiLog($code, 500, "user_id null", $database);

            } elseif ($user_id == -1) {
                JsonPrint(403, "access deny", null);
                tokenApiLog($code, 403, "access deny", $database);
            } else {
                $token = createToken($user_id, $openid);
                $retdata = new Token($token);
                JsonPrint(200, null, $retdata);
                tokenApiLog($code, 200, null, $database);
            }
        }
    }
}


