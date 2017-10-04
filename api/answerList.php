<?php
//以token和问题id，获取某问题下的缩略回答列表

require "../config/database_info_local.php";//数据库配置
require "../controller/json.php";//发送json模块
require "../controller/safe.php";//安全检查模块
require "../controller/decrypt.php"; //token解密模块
require "../controller/answer.php"; //answer操作模块
require "../controller/question.php"; //question操作模块
require "../controller/user.php";//user操作模块
require "../model/medoo.php";//数据库框架
require "../model/AnswerList.class.php"; //retdata对象
date_default_timezone_set('Asia/Shanghai');
$database = new medoo(array("database_name" => DATABASE_NAME));


if ($_GET == null || !is_array($_GET)) {
    JsonPrint(400, "post null", null);
} else {
    $token = isset($_GET["token"]) ? $_GET["token"] : null;
    $token = safe_check($token);
    $user_id = tokenDecrypt($token, $database);
    //uid 检查
    if ($user_id == null) {
        JsonPrint(400, "token error", null);
    } elseif (!isVaildUid($user_id, $database)) {
        JsonPrint(400, "token id error", null);
    } else {

        //qid检查
        $qid = isset($_GET["id"]) ? $_GET["id"] : null;
        $qid = safe_check($qid);
        if ($qid == null || !is_numeric($qid)) {
            JsonPrint(400, "question_id error", null);
        } elseif (!isVaildQid($qid, $database)) {
            JsonPrint(400, "question_id vaild error", null);
        } else {
            //获取问题对象
            $questionObj = getQuestionByQid($qid, $database);
            if ($questionObj == null) {
                JsonPrint(500, "get question_obj error", null);
            } else {
                //获取回答对象数组
                $answerList = getAnswerListByQid($qid, $user_id, $database);
                if ($answerList == -1) {
                    JsonPrint(500, "get answer_list error", null);
                } else {
                    $is_mine = isMyQuestion($user_id, $qid, $database);
                    //建立一个list对象
                    $retdata = new AnswerList($is_mine, $questionObj, $answerList);
                    JsonPrint(200, null, $retdata);
                }
            }
        }

    }
}

?>