<?php
/**以token和问题id 鉴定是否已经回答过该问题（回答权限）
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-19
 * Time: 15:21
 */

require "../config/database_info_local.php";//数据库配置
require "../controller/json.php";//发送json模块
require "../controller/decrypt.php";//token解密
require "../controller/answer.php";//对answer表的操作模块
require "../controller/question.php";//question表模块
require "../controller/user.php";//user表操作模块
require "../controller/safe.php";//安全检查模块
require "../model/medoo.php";//数据库框架
date_default_timezone_set('Asia/Shanghai');
$database = new medoo(array("database_name" => DATABASE_NAME));

if ($_GET == null || !is_array($_GET)) {
    JsonPrint(400, "get null", null);
} else {
    $token = isset($_GET["token"]) ? $_GET["token"] : null;
    $token = safe_check($token);
    $user_id = tokenDecrypt($token, $database);

    //userId 检验
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
            $question_id = isset($_GET["id"]) ? $_GET["id"] : null;
            $question_id = safe_check($question_id);

            //question_id检查
            if (is_null($question_id) || !is_numeric($question_id)) {
                JsonPrint(400, "question_id error", null);
            } elseif (isVaildQid($question_id, $database) == false) {
                JsonPrint(400, "question_id does not exist", null);
            } else {
                //用户性别是否与问题相异性匹配
                $sex_user = getUserSexByUid($user_id, $database);
                if (is_null($sex_user)) {
                    JsonPrint(500, "get user sex error", null);
                } else {
                    $sex_ask = getQuestionSexByQid($question_id, $database);
                    if (is_null($sex_ask)) {
                        JsonPrint(500, "get sex of question owner error", null);
                    } else {
                        if ($sex_user == $sex_ask) {
                            JsonPrint(406, "sex of user and question owner is the same", null);
                        } else {
                            //自问自答检查
                            $is_mine = isMyQuestion($user_id, $question_id, $database);
                            if ($is_mine) {
                                JsonPrint(404, "this is your question, answer failed", null);
                            } else {
                                //回答是否已经结束
                                $is_over = hasBestAnswer($question_id, $database);
                                if ($is_over) {
                                    JsonPrint(402, "this question is over and has a best answer", null);
                                } else {
                                    //每题限答一次检查
                                    $limit = hasAnswerThis($user_id, $question_id, $database);
                                    if (is_null($limit)) {
                                        JsonPrint(500, "data in DB error,not one answer to one question", null);
                                    } elseif ($limit == true) {
                                        JsonPrint(403, "already answer this question", null);
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