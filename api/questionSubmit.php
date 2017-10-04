<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-20
 * Time: 15:21
 */

require "../config/database_info_local.php";//数据库配置
require "../config/qcloud.php";
require '../qcloudapi/src/QcloudApi/QcloudApi.php';

require "../model/medoo.php";//数据库框架
require "../controller/json.php";//发送json模块
require "../controller/safe.php";//安全检查模块
require "../controller/decrypt.php";//token解密模块
require "../controller/question.php"; //对question表的操作模块
require "../controller/user.php"; //对user表的操作模块
//屏蔽
error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('Asia/Shanghai');
$database = new medoo(array("database_name" => DATABASE_NAME));

if ($_POST == null || !is_array($_POST)) {
    JsonPrint(400, "post null", null);
} else {
    $token = isset($_POST["token"]) ? $_POST["token"] : null;
    $token = safe_check($token);
    $user_id = tokenDecrypt($token, $database);
    // (0 == null) true
    if ($user_id == null) {
        JsonPrint(400, "token error", null);
    } elseif (!isVaildUid($user_id, $database)) {
        JsonPrint(400, "token id error", null);
    } else {
        //鉴权
        $limit = hasQuestionSubmitLimit($user_id, $database);
        if (is_null($limit)) {
            JsonPrint(500, "check question limit error", null);
        } elseif ($limit == true) {
            JsonPrint(402, "be limited to submit question", null);
        } else {
            $title = isset($_POST["title"]) ? $_POST["title"] : null;
            $title = safe_check($title);
            if(is_null($title)||$title==""){
                JsonPrint(400,"title null",null);
            }elseif (titleIsInLength($title) == false) {
                //50字长度
                JsonPrint(403, "title is not in length", null);
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
                        JsonPrint(500, "get user info error ", null);
                    } else {
                        //屏蔽
                        if (!warning($title)) {
                            //入库提交
                            $submit = questionSubmit($user_id, $title, $info, $database);
                            if (is_null($submit)) {
                                JsonPrint(500, "info array null", null);
                            } elseif ($submit == false) {
                                JsonPrint(500, "question submiting error", null);
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

function warning($text)
{
    $hasWarning = true;
    $check = isNoWarning($text);
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
           // echo "Error code:" . $error->getCode() . ".\n";
           //  echo "message:" . $error->getMessage() . ".\n";
//             echo "ext:" . var_export($error->getExt(), true) . ".\n";
        } else {
            $typeA = $a ["sensitive"];
        }

        if ($p === false) {
            $error = $wenzhi->getError();
            $typeP = 0.49;
//            echo "Error code:" . $error->getCode() . ".\n";
//             echo "message:" . $error->getMessage() . ".\n";
//             echo "ext:" . var_export($error->getExt(), true) . ".\n";
        } else {
            $typeP = $p ["sensitive"];
        }
        $limit = 68;
        //echo $typeP;
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
