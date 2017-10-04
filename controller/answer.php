<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-20
 * Time: 16:18
 */
date_default_timezone_set('Asia/Shanghai');
require "../model/Answer.class.php";


function noticeOfMyAnswer($user_id,$myNum,$database)
{
    $table_notice = "qa_noticecheck_answer";
    $data = $database->get($table_notice, [
        "num"
    ], [
        "AND" => [
            "uid" => $user_id,
            "visible" => 1
        ],
        "ORDER" => [
            "check_start" => "DESC"
        ]
    ]);
    if ($data === false || sizeof($data) == 0) {
        $setting = setAnswerMarkTime($user_id, time(), $myNum, $database);
        if (!$setting) {
            return null;
        } else {
            //0条新消息
            return $myNum;
        }
    } else if (!is_array($data)) {
        return null;
    } else {
        $markNum =$data["num"];
        return $myNum-$markNum;

    }
}



/**检查aid是否为指定qid的问题的最佳回答
 * @param $qid
 * @param $aid
 * @param $database
 * @return mixed
 */
function isBestAnswer($qid,$aid ,$database){
    $table = "qa_answer";
    $has = $database->has($table,[
        "id"=>$aid,
        "isBestAnswer"=>1,
        "qid"=>$qid,
        "visible"=>1
    ]);
    return $has;
}




/** 记录点击我的回答的时间 用作新提醒
 * @param $user_id
 * @param $timestamp
 * @param $database
 * @return bool
 */
function setAnswerMarkTime($user_id, $timestamp,$best_num,$database)
{
    $table = "qa_noticecheck_answer";
    $insert_id = $database->insert($table, [
        "uid" => $user_id,
        "check_start" => date("Y-m-d H:i:s", $timestamp),
        "num"=> $best_num,
        "visible" => 1
    ]);
    return (is_numeric($insert_id) && $insert_id != 0) ? true : false;
}


/** 找到我回答过的问题的qid数组
 * @param $user_id
 * @param $database
 * @return array|int|null
 */
function getMyAnswerQidArrayByUid($user_id, $database)
{
    $table = "qa_answer";
    unset($qids);
    $qids = array();
    $data = $database->select($table, [
        "qid"
    ], [
        "AND" => [
            "uid" => $user_id,
            "visible" => 1
        ]
    ]);
    if (!is_array($data)) {
        return -1;
    } elseif (sizeof($data) == 0) {
        return null;
    } else {
        foreach ($data as $d) {
            $qid = $d["qid"];
            $qids[] = $qid;
        }
        return $qids;
    }


}


/** 以aid和用户id获取一个answer对象
 * @param $answer_id
 * @param $user_id
 * @param $database
 * @return Answer|null
 */
function getAnswerByAid($answer_id, $user_id, $database)
{
    $table = "qa_answer";
    $data = $database->select($table, [
        "text",
        "time",
        "grade",
        "region",
        "isBestAnswer"
    ], [
        "AND" => [
            "id" => $answer_id,
            "visible" => 1
        ]
    ]);
    if (!is_array($data) || sizeof($data) != 1) {
        return null;
    } else {
        foreach ($data as $d) {
            $text = $d["text"];
            $time_db = $d["time"];
            $time = date("m-d H:i", strtotime($time_db));
            $grade = $d["grade"];
            $region = $d["region"];
            $isBest = $d["isBestAnswer"];
            //string to boolean
            $isBestAnswer = $isBest == 1 ? true : false;
            $isMyAnswer = isMyAnswer($answer_id, $user_id, $database);
            //打包
            $answerObj = new Answer($answer_id, $text, $time, $grade, $region, $isBestAnswer, $isMyAnswer);
            return $answerObj;
        }
    }
}

/** 以问题id获取答主id
 * @param $answer_id
 * @param $database
 * @return null
 */
function getAnswerUidByAid($answer_id, $database)
{
    $table = "qa_answer";
    $data = $database->select($table, [
        "uid"
    ], [
        "AND" => [
            "id" => $answer_id,
            "visible" => 1
        ]
    ]);
    if (!is_array($data) || sizeof($data) != 1) {
        return null;
    } else {
        foreach ($data as $d) {
            $answer_uid = $d["uid"];
            return $answer_uid;
        }
    }

}


/** 判断qid和aid是否为匹配的一对
 * @param $qid
 * @param $aid
 * @param $database
 * @return bool
 */
function isQAMatch($qid, $aid, $database)
{
    $table = "qa_answer";
    $has = $database->has($table, [
        "AND" => [
            "id" => $aid,
            "qid" => $qid,
            "visible" => 1
        ]
    ]);
    return $has;
}

/** 在answer表内设置被选为最佳
 * @param $aid
 * @param $database
 * @return bool
 */
function setABestAnswer($aid, $database)
{
    $table = "qa_answer";
    $row_num = $database->update($table, [
        "isBestAnswer" => 1,
        "best_time" => date("Y-m-d H:i:s", time())
    ], [
        "AND" => [
            "id" => $aid,
            "visible" => 1
        ]
    ]);
    return $row_num == 1 ? true : false;

}

/**以qid获取该题下的所有缩略问答list
 * @param $qid
 * @param $user_id
 * @param $database
 * @return array|int|null 有回答是对象数组，-1报错，null无回答
 */
function getAnswerListByQid($qid, $user_id, $database)
{
    $table = "qa_answer";
    $data = $database->select($table, [
        "id",
        "text",
        "time",
        "grade",
        "region",
        "isBestAnswer"
    ], [
        "AND" => [
            "qid" => $qid,
            "visible" => 1
        ]
    ]);

    //var_dump($data);
    if (!is_array($data)) {
        return -1;
    } elseif (sizeof($data) == 0) {
        return $data;
    } else {
        unset($answerList);
        $answerList = array();
        foreach ($data as $d) {
            $answer_id = $d["id"];
            $text_db = $d["text"];
            $part_text = mb_substr($text_db, 0, 50, 'UTF-8');
            if (mb_strlen($part_text) == 50) {
                $part_text = $part_text . "...";
            }
            $time_db = $d["time"];
            $time = date("m-d H:i", strtotime($time_db));
            $grade = $d["grade"];
            $region = $d["region"];
            $isBest = $d["isBestAnswer"];
            //string to boolean
            $isBestAnswer = $isBest == 1 ? true : false;
            $isMyAnswer = isMyAnswer($answer_id, $user_id, $database);
            //打包
            $answer = new Answer($answer_id, $part_text, $time, $grade, $region, $isBestAnswer, $isMyAnswer);
            $answerList[] = $answer;
        }
        //var_dump($answerList);
        return $answerList;
    }
}

/** 判断是否是我的回答
 * @param $answer_id
 * @param $user_id
 * @param $database
 * @return boolean
 */
function isMyAnswer($answer_id, $user_id, $database)
{
    $table = "qa_answer";
    $has = $database->has($table, [
        "AND" => [
            "id" => $answer_id,
            "uid" => $user_id,
            "visible" => 1
        ]
    ]);
    return $has;

}

/**检查aid是否有效
 * @param $answer_id
 * @param $database
 * @return bool
 */
function isVaildAid($answer_id, $database)
{
    $table = "qa_answer";
    if (!is_numeric($answer_id)) {
        return false;
    } else {
        $has = $database->has($table, [
            "AND" => [
                "id" => $answer_id,
                "visible" => 1
            ]
        ]);
        return $has;
    }

}

//
//function answerSubmitApiLog($question_id, $answer_id, $text, $answer_user_id, $database){
//    $table_log ="qa_log_api_answersubmit";
//    $insert_id = $database->insert($table_log,[
//        "qid"=>$question_id,
//        "aid"=>$answer_id,
//        "text"=>$text,
//        "answer_uid" => $answer_user_id,
//        "time"=>date("Y-m-d H:i:s",now()),
//        "visible"=>1
//    ]);
//    if (is_numeric($insert_id) && $insert_id != 0) {
//        return true;
//    } else {
//        return false;
//    }
//}

//
///**以回答内容找到回答的id
// * @param $text
// * @param $database
// * @return $answer_id null 报错
// */
//function getAnswerIdByText($text, $answer_uid, $database)
//{
//    global $table;
//    $data = $database->select($table, [
//        "id"
//    ], [
//        "AND" => [
//            "text" => $text,
//            "uid" => $answer_uid,
//            "visible" => 1
//        ]
//    ]);
//    if(empty($data)){
//        return null;
//    }else{
//        foreach ($data as $d){
//            $answer_id = $d["id"];
//            return is_numeric($answer_id)?$answer_id:null;
//        }
//    }
//
//}


/**向指定的问题 提交回答
 * @param $question_id
 * @param $text
 * @param $user_id
 * @param $info
 * @param $database
 * @return bool|null null报错
 */

function answerSubmit($question_id, $text, $user_id, $info, $database)
{
    $table = "qa_answer";

    if ($info == null) {
        return null;
    } else {
        $sex = $info["sex"];
        $region = $info["region"];
        $grade = $info["grade"];

        $insert_id = $database->insert($table, [
            "text" => $text,
            "time" => date("Y-m-d H:i:s", time()),
            "uid" => $user_id,
            "sex" => $sex,
            "grade" => $grade,
            "region" => $region,
            "isBestAnswer" => 0,
            "qid" => $question_id,
            "visible" => 1
        ]);
        if (is_numeric($insert_id) && $insert_id != 0) {
            return true;
        } else {
            return false;
        }

    }
}

/**字数限制
 * @param $text
 * @param int $limit
 */
function textIsInLength($text, $limit = 650)
{
    return mb_strlen($text, 'UTF-8') <= $limit ? true : false;
}


/** 检查用户是否已经回答过该问题
 * @param $user_id int 用户id
 * @param $question_id  int 问题的id
 * @param $database object
 * @return boolean null报错 true已回答 false未回答
 */
function hasAnswerThis($user_id, $question_id, $database)
{
    $table = "qa_answer";
    $data = $database->select($table, [
        //以time鉴别是否一问多答（不可能情况 500error）
        "time"
    ], [
        "AND" => [
            "uid" => $user_id,
            "qid" => $question_id,
            "visible" => 1
        ]
    ]);
    if (!is_array($data) || sizeof($data) > 1) {
        return null;
    } elseif (sizeof($data) == 1 && !is_null($data[0]["time"])) {
        return true;
    } else {
        return false;
    }
}