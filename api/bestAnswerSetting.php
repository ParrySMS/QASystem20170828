<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-23
 * Time: 0:37
 */
require "../config/database_info_local.php";//数据库配置
require "../controller/json.php";//发送json模块
require "../controller/safe.php";//安全检查模块
require "../controller/decrypt.php"; //token解密模块
require "../controller/answer.php"; //answer操作模块
require "../controller/question.php"; //question操作模块
require "../controller/user.php";//user操作模块
require "../model/medoo.php";//数据库框架

date_default_timezone_set('Asia/Shanghai');
$database = new medoo(array("database_name" => DATABASE_NAME));


if ($_POST == null || !is_array($_POST)) {
    JsonPrint(400, "post null", null);
} else {
    unset($user_id);
    $token = isset($_POST["token"]) ? $_POST["token"] : null;
    $token = safe_check($token);
    $user_id = tokenDecrypt($token, $database);
    if ($user_id == null) {
        JsonPrint(400, "token error", null);
    } elseif (!isVaildUid($user_id, $database)) {
        JsonPrint(400, "token id error", null);
    } else {
        $qid = safe_check(isset($_POST["question_id"]) ? $_POST["question_id"] : null);
        $aid = safe_check(isset($_POST["answer_id"]) ? $_POST["answer_id"] : null);
//        $answer_uid = safe_check(isset($_POST["answer_uid"]) ? $_POST["answer_uid"] : null);
        if (!isVaildQid($qid, $database)) {
            JsonPrint(400, "question_id is null or is not vaild", null);
        } elseif (!isVaildAid($aid, $database)) {
            JsonPrint(400, "answer_id is null or not vaild", null);
        } //elseif (!isVaildUid($answer_uid, $database)) {
        //JsonPrint(400, "answer_uid is null or is not vaild", null);
        //}
    else {
            //qid与aid是否匹配
            $is_match = isQAMatch($qid, $aid, $database);
            if (!$is_match) {
                JsonPrint(400, "qid and aid not match", null);
            } else {
                //题主权限
                $is_mine = isMyQuestion($user_id, $qid, $database);
                if (!$is_mine) {
                    JsonPrint(403, "this is not your question", null);
                } else {
                    //回答是否已经结束
                    $is_over = hasBestAnswer($qid, $database);
                    if ($is_over) {
                        JsonPrint(402, "this question is over and has a best answer", null);
                    } else {
                        //问题未结束 检查是不是自问自答
                        $is_self = isMyAnswer($aid, $user_id, $database);
                        if ($is_self) {
                            JsonPrint(500, "my question to my answer", null);
                        } else {
                            //answer表设置
                            $bestSetting = setABestAnswer($aid, $database);
                            if (!$bestSetting) {
                                JsonPrint(500, "Bestsetting error", null);
                            } else {
                                //question表
                                $hasBestAnswerSetting = setHasBestAnswer($qid, $database);
                                if (!$hasBestAnswerSetting) {
                                    JsonPrint(500, "setHasBestAnswer error", null);
                                } else {
                                    //user表
                                    //找到答主设置
                                    $answer_uid = getAnswerUidByAid($aid, $database);
                                    if (is_null($answer_uid)) {
                                        JsonPrint(500, "get answer_uid error", null);
                                    } else {
                                        $userBestAnswerSetting = addBestAnswerNum($answer_uid, $database);
                                        if (!$userBestAnswerSetting) {
                                            JsonPrint(500, "add answer_user best_answer_num error", null);
                                        } else {
                                            JsonPrint(200, null, null);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}