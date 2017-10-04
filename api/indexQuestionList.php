<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-23
 * Time: 15:34
 */
require "../config/database_info_local.php";//数据库配置
require "../controller/json.php";//发送json模块
require "../controller/safe.php";//安全检查模块
require "../controller/decrypt.php"; //token解密模块
require "../controller/answer.php"; //answer操作模块
require "../controller/question.php"; //question操作模块
require "../controller/user.php";//user操作模块
require "../controller/log.php";//记录模块
require "../model/QuestionList.class.php";//问题对象数组
require "../model/medoo.php";//数据库框架

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
        $sex_ask = safe_check(isset($_GET["sex_ask"]) ? $_GET["sex_ask"] : null);
        $page = safe_check(isset($_GET["page"]) ? $_GET["page"] : null);

        if (is_null($sex_ask) || !is_numeric($sex_ask)) {
            JsonPrint(400, "sex of the question owner error", null);
        } elseif (is_null($page) || !is_numeric($page)) {
            JsonPrint(400, "page of the question error", null);
        } else {
            //获取所有的问题对象数组
            $all_qlist = getAllQuestionListBySex($sex_ask, $database);
            if ($all_qlist == -1) {
                JsonPrint(500, "check all qlist error", null);
            } elseif (is_null($all_qlist)) {
                JsonPrint(403, "qlist in this sex is null", null);
            } else {
                //查找log记录 获取要删除的qid数组 可能为空
                $delete_qids = getQidArrayInLog($user_id, $page, $sex_ask,$database);
                if ($delete_qids == -1) {
                    JsonPrint(500, "get qid log error", null);
                } else {
                    //用总的问题对象数组打乱 删去已经发过的
                    $index_list = getIndexQuestionList($all_qlist, $delete_qids);
                    if ($index_list == -1) {
                        JsonPrint(500, "get indexlist error", null);
                    } elseif (is_null($index_list)) {
                        JsonPrint(403, "no more data", null);
                    } else {
                        //log 记录下这次发出去的qid数组
                        $log_status = logIndexListQid($index_list, $user_id,$sex_ask,$database);
                        if (!$log_status) {
                            JsonPrint(500, "log indexlist error", null);
                        } else {
                            $retdata = new QuestionList($index_list);
                            //var_dump($delete_qids);
                            JsonPrint(200, null, $retdata);
                        }
                    }

                }
            }


        }
    }
}
