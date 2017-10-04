<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-24
 * Time: 22:48
 */
require "../config/database_info_local.php";//数据库配置
require "../controller/json.php";//发送json模块
require "../controller/safe.php";//安全检查模块
require "../controller/decrypt.php";//token解密
require "../controller/question.php"; //对question表的操作模块
require "../controller/user.php"; //对user表的操作模块
require "../model/medoo.php";//数据库框架
require "../model/QuestionList.class.php";
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
        //echo $user_id;
        $status = UserSettingIsDone($user_id, $database);
        if (is_null($status)) {
            JsonPrint(500, "token vaild but user error", null);
        } elseif ($status == false) {
            JsonPrint(403, "need finish UserSetting", null);
        } else {

            $timestamp = time();//先记录时间
            $questionList_ask = getMyAskQuestionList($user_id,$database);
            if($questionList_ask == -1){
                JsonPrint(500,"myAskQuestionList error",null);
            }else {
                //更新记录当前回答数量
                $num = getTotalAnswerNumByQList($questionList_ask);
                //这里用list去取当前回答数量的原因是 要保证取到的list和对应的时间戳 回答数 是匹配的
                //即使在取list瞬时之后据库有新操作也不会影响
                $marktime_setting = setQuestionMarkTime($user_id, $timestamp,$num, $database);
                if (!$marktime_setting) {
                    JsonPrint(500,"set mark time of answer error",null);
                } else {
                    $retdata = new QuestionList($questionList_ask);
                    JsonPrint(200, null, $retdata);
                }
            }
        }
    }
}