<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-25
 * Time: 0:46
 */

require "../config/database_info_local.php";//数据库配置
require "../config/option.php"; //选项配置文件
require "../controller/json.php";//发送json模块
require "../controller/safe.php";//安全检查模块
require "../controller/decrypt.php";//token解密模块
require "../controller/question.php"; //对question表的操作模块
require "../controller/answer.php"; //对answer表的操作模块
require "../controller/user.php"; //对user表的操作模块
require "../model/medoo.php";//数据库框架
require "../model/QuestionList.class.php";

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
        //echo $user_id;
        $status = UserSettingIsDone($user_id, $database);
        if (is_null($status)) {
            JsonPrint(500, "token vaild but user error", null);
        } elseif ($status == false) {
            JsonPrint(403, "need finish UserSetting", null);
        } else {
            $qids_of_my_answer = getMyAnswerQidArrayByUid($user_id, $database);
            if ($qids_of_my_answer == -1) {
                JsonPrint(500, "get qids error", null);
            } else {
                $timestamp = time();
                $quertionList_answer = getQuestionListByQidArray($qids_of_my_answer, $database);
                if ($quertionList_answer == -1) {
                    JsonPrint(500, "get questionList of my answer error", null);
                } else {
                    $best_answer_num = getMyBestAnswerNum($user_id, $database);
                    if (!is_numeric($best_answer_num)) {
                        JsonPrint(500, "getMyBestAnswerNum error", null);
                    } else {
                        $marktime_setting = setAnswerMarkTime($user_id, $timestamp, $best_answer_num, $database);
                        if (!$marktime_setting) {
                            JsonPrint(500, "set mark_time of my answer error", null);
                        } else {
                            $retdata = new QuestionList($quertionList_answer);
                            JsonPrint(200, null, $retdata);
                        }
                    }
                }
            }
        }
    }
}