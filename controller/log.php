<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-23
 * Time: 17:38
 */

function logIndexListQid($index_list, $user_id, $sex_ask, $database)
{
    $table = "qa_log_api_indexquestionlist";
    unset($log_ids);
    $log_ids = array();
    foreach ($index_list as $question) {
        $qid = $question->id;
        $log_id = $database->insert($table, [
            "uid" => $user_id,
            "qid" => $qid,
            "sex_ask" => $sex_ask,
            "time" => date("Y-m-d H:i:s", time()),
            "visible" => 1
        ]);
        $log_ids[] = $log_id;
    }
    return sizeof($log_ids) <= 10 ? true : false;

}


/** 记录拿code请求的log
 * @param $code
 * @param $retcode
 * @param null $retmsg
 * @param $database
 */
function tokenApiLog($code, $retcode, $retmsg = null, $database)
{
    $table = "qa_log_api_token";
    $insert = $database->insert($table, [
        "code" => $code,
        "retcode" => $retcode,
        "retmsg" => $retmsg,
        "time" => date("Y-m-d H:i:s", time()),
        "visible" => 1
    ]);
    //echo $insert;
}


/** 获取page页数前的请求记录qid数组
 * @param $user_id
 * @param $page
 * @param $database
 * @return array|int  -1则报错
 */

function getQidArrayInLog($user_id, $page, $sex_ask, $database)
{
    $num_each_page = 10;
    //一页十个
    if ((int)$page == 1) {
        return array();
    } else {
        unset($check_rows);
        $check_rows = $num_each_page * ($page - 1);
        //查之前页码请求过的内容
        $table = "qa_log_api_indexquestionlist";
        $data = $database->select($table, [
            "qid"
        ], [
            "AND" => [
                "uid" => $user_id,
                "sex_ask" => $sex_ask,
                "visible" => 1
            ],
            "ORDER"=>[
                "time"=>"DESC"
            ]
        ]);
        if (!is_array($data) || sizeof($data) == 0) {
            return -1;
        } else {
            unset($qidArray);
            $qidArray = array();
            foreach ($data as $d) {
                $qid = $d["qid"];
                $qidArray[] = $qid;
                if (sizeof($qidArray) == $check_rows) {
                    break;
                }
            }
            return $qidArray;
        }
    }
}