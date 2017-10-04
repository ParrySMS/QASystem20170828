<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-20
 * Time: 18:45
 */
require "../config/database_info_local.php";//数据库配置
require "../config/qcloud.php";
require '../qcloudapi/src/QcloudApi/QcloudApi.php';
//屏蔽
require "../model/medoo.php";//数据库框架
require "../controller/json.php";//发送json模块
require "../controller/safe.php";//安全检查模块
require "../controller/decrypt.php"; //token解密模块
require "../controller/answer.php"; //answer操作模块
require "../controller/question.php"; //question操作模块
require "../controller/user.php";//user操作模块
//require "../qcloudapi/point.php";//屏蔽部分
error_reporting(E_ALL ^ E_NOTICE);
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
        $qid = isset($_POST["id"]) ? $_POST["id"] : null;
        $qid = safe_check($qid);
        //qid检查
        if (is_null($qid) || !is_numeric($qid)) {
            JsonPrint(400, "question_id error", null);
        } elseif (isVaildQid($qid, $database) == false) {
            JsonPrint(400, "question_id does not exist", null);
        } else {
            $sex_ask = safe_check(isset($_POST["sex"]) ? $_POST["sex"] : null);
            if (is_null($sex_ask)) {

            } else {
                //鉴权
				
                //限制每题回答一次
                $limit_aw = hasAnswerThis($user_id, $qid, $database);
                if (is_null($limit_aw)) {
                    JsonPrint(500, "check limit_aw error", null);
                } elseif ($limit_aw == true) {
                    JsonPrint(405, "has answer this question", null);
                } else {
                    $limit_self = isMyQuestion($user_id, $qid, $database);

                    //自问自答检查
                    $is_mine = isMyQuestion($user_id, $qid, $database);
                    if ($is_mine) {
                        JsonPrint(404, "this is your question, answer failed", null);
                    } else {
                        //回答是否已经结束
                        $is_over = hasBestAnswer($qid, $database);
                        if ($is_over) {
                            JsonPrint(402, "this question is over and has a best answer", null);
                        } else {
								
                            //回答内容检查
                            $text = isset($_POST["text"]) ? $_POST["text"] : null;
                            $text = safe_check($text);
                            if ($text == null || $text == "") {
                                JsonPrint(400, "text null", null);
                            } elseif (textIsInLength($text) == false) {
                                //500字
                                JsonPrint(403, "text is not in length", null);
                            } else {
                                //检验用户信息设置
                                $info_setting = UserSettingIsDone($user_id, $database);
                                if (is_null($info_setting)) {
                                    JsonPrint(500, "token vaild but user error", null);
                                } elseif ($info_setting == false) {
                                    JsonPrint(401, "need finish UserSetting", null);
                                } else {
                                    //已设置 请求信息
                                    $info = getUserInfoArrayById($user_id, $database);
                                    if ($info == null) {
                                        JsonPrint(500, "get user info error", null);
                                    } else {

                                        //用户性别是否与问题相异性匹配
                                        $sex_user = getUserSexByUid($user_id, $database);
                                        if (is_null($sex_user)) {
                                            JsonPrint(500, "get user sex error", null);
                                        } else {
                                            $sex_ask = getQuestionSexByQid($qid, $database);
                                            if (is_null($sex_ask)) {
                                                JsonPrint(500, "get sex of question owner error", null);
                                            } else {
                                                if ($sex_user == $sex_ask) {
                                                    JsonPrint(406, "sex of user and question owner is the same", null);
                                                } else {
												
                                                    //敏感屏蔽
													
                                                    if (!warning($text)) {
															
                                                        //入库提交
                                                        $submit = answerSubmit($qid, $text, $user_id, $info, $database);
                                                        if (is_null($submit)) {
                                                            JsonPrint(500, "info array null", null);
                                                        } elseif ($submit == false) {
                                                            JsonPrint(500, "answer submiting error", null);
                                                        } else {
                                                            $status = changeAnswerNumByQid($qid, $database);
                                                            if ($status == false) {
                                                                JsonPrint(500, "change answer_num error", null);
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
                }
            }
        }
    }
}

function warning($text)
{	
	
    $hasWarning = true;
    $check = isNoWarning($text);
	//var_dump($check);
    if (is_null($check)) {
        JsonPrint(500, "warning check error", null);
    } else {
        switch ($check) {
            case 1:
                JsonPrint(420, "可能含有不健康内容，建议修改", null);
                break;
            case 2:
                JsonPrint(421, "可能含有政治敏感内容，建议修改", null);
                break;
            default:
                $hasWarning = false;
                break;
        }
    }
    return $hasWarning;
}


function isNoWarning($content)
{
    //unset($content);
    //$content = isset($_GET["content"]) ? $_GET["content"] : null;
    if (is_null($content)) {
        return null;
    } else {
        $content = safe_check($content);
        $config = array(
            'SecretId' => SECRETID,
            'SecretKey' => SECRETKEY,
            'RequestMethod' => 'POST',
            'DefaultRegion' => 'gz');

        $wenzhi = QcloudApi::load(QcloudApi::MODULE_WENZHI, $config);

        $packageA = array("content" => $content, "type" => 1);
        $packageP = array("content" => $content, "type" => 2);

        $a = $wenzhi->TextSensitivity($packageA);
        $p = $wenzhi->TextSensitivity($packageP);

        if ($a === false) {
            $error = $wenzhi->getError();
            $typeA = 0.49;
            echo "Error code:" . $error->getCode() . ".\n";
             echo "message:" . $error->getMessage() . ".\n";
             echo "ext:" . var_export($error->getExt(), true) . ".\n";
        } else {
            $typeA = $a ["sensitive"];
        }

        if ($p === false) {
            $error = $wenzhi->getError();
            $typeP = 0.49;
            echo "Error code:" . $error->getCode() . ".\n";
             echo "message:" . $error->getMessage() . ".\n";
             echo "ext:" . var_export($error->getExt(), true) . ".\n";
        } else {
            $typeP = $p ["sensitive"];
        }
        $limit = 68;

        if (120 * $typeA > $limit) {
            return 1;
            //色情
        } elseif (110 * $typeP > $limit) {
            return 2;
            //政治敏感
        } else {
            return 0;
        }

    }
}
