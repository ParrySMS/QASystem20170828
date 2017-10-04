<?php
/**通过token，问题id，回答id，获取详情页信息
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-23
 * Time: 13:26
 */
require "../config/database_info_local.php";//数据库配置
require "../controller/json.php";//发送json模块
require "../controller/safe.php";//安全检查模块
require "../controller/decrypt.php"; //token解密模块
require "../controller/answer.php"; //answer操作模块
require "../controller/question.php"; //question操作模块
require "../controller/user.php";//user操作模块
require "../model/medoo.php";//数据库框架
require "../model/AnswerToQuestion.class.php";//一问一答信息类

date_default_timezone_set('Asia/Shanghai');
$database = new medoo(array("database_name" => DATABASE_NAME));


if ($_GET == null || !is_array($_GET)) {
    JsonPrint(400, "post null", null);
} else {
    $token = isset($_GET["token"]) ? $_GET["token"] : null;
    $token = safe_check($token);
    $user_id = tokenDecrypt($token, $database);
    if ($user_id == null) {
        JsonPrint(400, "token error", null);
    } elseif (!isVaildUid($user_id, $database)) {
        JsonPrint(400, "token id error", null);
    } else {
        $qid = safe_check(isset($_GET["question_id"]) ? $_GET["question_id"] : null);
        $aid = safe_check(isset($_GET["answer_id"]) ? $_GET["answer_id"] : null);
        if (!isVaildQid($qid, $database)) {
            JsonPrint(400, "question_id is null or is not vaild", null);
        } elseif (!isVaildAid($aid, $database)) {
            JsonPrint(400, "answer_id is null or not vaild", null);
        } else {
            //qid与aid是否匹配
            $is_match = isQAMatch($qid, $aid, $database);
            if (!$is_match) {
                JsonPrint(400, "qid and aid not match", null);
            } else {
                $questionObj = getQuestionByQid($qid, $database);
                if (is_null($questionObj)) {
                    JsonPrint(500, "question Obj error", null);
                } else {
                    $answerObj = getAnswerByAid($aid, $user_id, $database);
                    if (is_null($answerObj)) {
                        JsonPrint(500, "answer Obj error", null);
                    } else {
                        $isMyQuestion = isMyQuestion($user_id, $qid, $database);
                        $retdata = new AnswerToQuestion($isMyQuestion,$questionObj,$answerObj);
                        //var_dump($retdata);
                        JsonPrint(200,null,$retdata);
                    }
                }
            }
        }
    }
}