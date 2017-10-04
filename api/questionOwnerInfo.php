<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-25
 * Time: 16:38
 */

require "../config/database_info_local.php";//数据库配置
require "../controller/json.php";//发送json模块
require "../controller/safe.php";//安全检查模块
require "../controller/decrypt.php"; //token解密模块
require "../controller/answer.php"; //answer操作模块
require "../controller/question.php"; //question操作模块
require "../controller/user.php";//user操作模块
require "../model/medoo.php";//数据库框架
require "../model/UserInfo.class.php";


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
                //自问自答
                $isMyQuestion = isMyQuestion($user_id, $qid, $database);
                if ($isMyQuestion) {
                    JsonPrint(404, "this is your question", null);
                } else {
                    //答主用户信息
                    $status = UserSettingIsDone($user_id, $database);
                    if (is_null($status)) {
                        JsonPrint(500, "token vaild but user error", null);
                    } elseif ($status == false) {
                        JsonPrint(500, "no userInfo but have an answer submitted", null);
                    } else {
                        //是否问题最佳匹配问答
                        $is_best = isBestAnswer($qid, $aid, $database);
                        if ($is_best) {
                            JsonPrint(403, "this is not the best answer of this question ", null);
                        } else {
                            $answer_uid = getAnswerUidByAid($aid, $database);
                            if (is_null($answer_uid)) {
                                JsonPrint(500, "get answer_uid error", null);
                            } elseif (!isVaildUid($answer_uid, $database)) {
                                JsonPrint(500, "answer_uid error", null);
                            } else {
                                //是否为答主
                                $is_mine = isMyAnswer($aid, $user_id, $database);
                                if (!$is_mine) {
                                    JsonPrint(401, "this is not you answer", null);
                                } else {
                                    //获取题主id
                                    $question_uid = getQuestionUidByQid($qid, $database);
                                    if (is_null($question_uid) || !is_numeric($question_uid)) {
                                        JsonPrint(500, "get question owner uid error", null);
                                    } elseif (!isVaildUid($question_uid, $database)) {
                                        JsonPrint(500, "question uid is not vaild", null);
                                    } else {
                                        //检查题主信息是否完全填写
                                        $infoSetting = userSettingIsDone($question_uid, $database);
                                        if (!$infoSetting) {
                                            JsonPrint(500, "question owner info need finishing but have a question submitted", null);
                                        } else {
                                            $userInfoArray = getUserInfoArrayById($question_uid, $database);
                                            $sex = $userInfoArray["sex"];
                                            $region = $userInfoArray["region"];
                                            $grade = $userInfoArray["grade"];
                                            $wechat = $userInfoArray["wechat"];
                                            $phone = $userInfoArray["phone"];
                                            $retdata = new UserInfo($sex, $region, $grade, $wechat, $phone);
                                            JsonPrint(200, null, $retdata);
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