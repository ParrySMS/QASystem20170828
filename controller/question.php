<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-20
 * Time: 16:14
 */

date_default_timezone_set('Asia/Shanghai');
require "../model/Question.class.php";


/**以用户id返回自己的新回答数
 * @param $user_id int 用户id
 * @return int  null报错
 */

function noticeOfMyQuestion($user_id, $num_now, $database)
{
    $table_notice = "qa_noticecheck_question";
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
        $setting = setQuestionMarkTime($user_id, time(), $num_now, $database);
        if (!$setting) {
            return null;
        } else {
            //0条新消息
            return $num_now;
        }
    } else if (!is_array($data)) {
        return null;
    } else {
        $num_mark = $data["num"];

        return $num_now - $num_mark;
    }
}


/**以获取我的提问对象数组 找到里面的回答数
 * @param $questionList
 * @return int
 */
function getTotalAnswerNumByQList($questionList)
{
    //var_dump($questionList);
    if(is_null($questionList)){
        return 0;
    }else {
        unset($num);
        $num = 0;
        foreach ($questionList as $qObj) {
            $answer_num = $qObj->answer_num;
            $num = $num + $answer_num;
        }
        return $num;
    }
}


/** 以用户id获取我的提问下总的问题回答数量
 * @param $user_id
 * @param $database
 * @return int|null
 */
function getTotalAnswerNumByUid($user_id, $database)
{
    $table = "qa_question";
    unset($num);
    $num = 0;
    $data = $database->select($table, [
        "answer_num"
    ], [
        "AND" => [
            "uid" => $user_id,
            "visible" => 1
        ]
    ]);
    if (!is_array($data)) {
        return null;
    } elseif (sizeof($data) == 0) {
        return $num;
    } else {
        foreach ($data as $d) {
            $answer_num = $d["answer_num"];
            $num = $num + $answer_num;
        }
        return $num;
    }
}


/** 获取指定问题的题主id
 * @param $question_id
 * @param $database
 * @return null
 */
function getQuestionUidByQid($question_id, $database)
{
    $table = "qa_question";
    $data = $database->select($table, [
        "uid"
    ], [
        "AND" => [
            "id" => $question_id,
            "visible" => 1
        ]
    ]);
    if (!is_array($data) || sizeof($data) != 1) {
        return null;
    } else {
        foreach ($data as $d) {
            $uid = $d["uid"];
            return $uid;
        }
    }
}

/** 获取指定问题的提问者的性别
 * @param $question_id
 * @param $database
 * @return null
 */
function getQuestionSexByQid($question_id, $database)
{
    $table = "qa_question";
    $data = $database->select($table, [
        "sex"
    ], [
        "AND" => [
            "id" => $question_id,
            "visible" => 1
        ]
    ]);
    if (!is_array($data) || sizeof($data) != 1) {
        return null;
    } else {
        foreach ($data as $d) {
            $sex = $d["sex"];
            return $sex;
        }
    }
}


/** 记录点击了我的提问的时间 用作新消息提醒
 * @param $user_id
 * @param $timestamp
 * @param $database
 * @return bool
 */
function setQuestionMarkTime($user_id, $timestamp, $num, $database)
{
    $table = "qa_noticecheck_question";
    $insert_id = $database->insert($table, [
        "uid" => $user_id,
        "check_start" => date("Y-m-d H:i:s", $timestamp),
        "num" => $num,
        "visible" => 1
    ]);
    return (is_numeric($insert_id) && $insert_id != 0) ? true : false;
}


/**根据answer表里找到我回答的qi的数组 获取我回答的问题list
 * @param $qids_of_my_answer
 * @param $database
 * @return array|int|null 可以null -1报错
 */
function getQuestionListByQidArray($qids_of_my_answer, $database)
{
    unset($my_answer_question_list);
    $my_answer_question_list = array();
    $table = "qa_question";
    if (sizeof($qids_of_my_answer) == 0) {
        return null;
    } else {
        foreach ($qids_of_my_answer as $qid) {
            $data = $database->select($table, [
                "title",
                "time",
                "answer_num",
                "sex",
                "grade",
                "region",
                "has_best_answer"
            ], [
                "AND" => [
                    "id" => $qid,
                    "visible" => 1
                ]
            ]);
            if (!is_array($data)) {
                return -1;
            } elseif (sizeof($data) == 0) {
                continue;
            } else {
                foreach ($data as $d) {
                    //数据塞进对象里
                    $title = $d["title"];
                    $time_ori = $d["time"];
                    $time = date("m-d H:i", strtotime($time_ori));
                    $answer_num = $d["answer_num"];
                    $sex = $d["sex"];
                    $grade = $d["grade"];
                    $region = $d["region"];
                    $hasBestAnswer = $d["has_best_answer"];
                    //string 变 boolean
                    $hasBestAnswer = $hasBestAnswer == 1 ? true : false;
                    $question = new Question($qid, $title, $time, $answer_num, $sex, $grade, $region, $hasBestAnswer);
                    $my_answer_question_list[] = $question;

                }
            }
        }//foreach

        return $my_answer_question_list;
    }

}

/** 获取我提问的问题list
 * @param $user_id
 * @param $database
 * @return array|int|null 可以null -1报错
 */
function getMyAskQuestionList($user_id, $database)
{
    $table = "qa_question";
    unset($my_ask_question_list);
    $my_ask_question_list = array();
    $data = $database->select($table, [
        "id",
        "title",
        "time",
        "answer_num",
        "sex",
        "grade",
        "region",
        "has_best_answer"
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
            //数据塞进对象里
            $qid = $d["id"];
            $title = $d["title"];
            $time_ori = $d["time"];
            $time = date("m-d H:i", strtotime($time_ori));
            $answer_num = $d["answer_num"];
            $sex = $d["sex"];
            $grade = $d["grade"];
            $region = $d["region"];
            $hasBestAnswer = $d["has_best_answer"];
            //string 变 boolean
            $hasBestAnswer = $hasBestAnswer == 1 ? true : false;
            $question = new Question($qid, $title, $time, $answer_num, $sex, $grade, $region, $hasBestAnswer);
            $my_ask_question_list[] = $question;
        }
        return $my_ask_question_list;
    }

}


/** 从总问题list中随机取十个并且删去qidArray里面的对象
 * @param $all_qlist array 总问题list
 * @param $qidArray  array 要删去避免选总的qid
 * @return array|int|null 可以返回空 -1报错
 */
function getIndexQuestionList($all_qlist, $qidArray)
{
    unset($indexList);
    $indexList = array();
    $num_each_page = 10;
    shuffle($all_qlist);
    //不用删id 直接取十个
    // var_dump($all_qlist);
    //var_dump($qidArray);

    if (is_array($qidArray) && sizeof($qidArray) == 0) {
        for ($num = 0; $num < $num_each_page; $num++) {
            $indexList[] = $all_qlist[$num];
        }
    } else {
        foreach ($all_qlist as $q) {
            $qid = $q->id;
            if (in_array($qid, $qidArray)) {
                continue;
            } else {
                $indexList[] = $q;
            }
            //foreach 大量数组时的跳出
            if (sizeof($indexList) == $num_each_page) {
                break;
            }
        }

    }
    if (sizeof($indexList) > 10) {
        return -1;
    } else if (sizeof($indexList) == 0) {
        return null;
    } else {
        return $indexList;
    }
}

/** 获取当前性别分区里的所有问题对象数组list
 * @param $sex_ask
 * @param $database
 * @return array|int|null
 */

function getAllQuestionListBySex($sex_ask, $database)
{
    $table = "qa_question";
    unset($question_list);
    $question_list = array();
    $data = $database->select($table, [
        "id",
        "title",
        "time",
        "answer_num",
        "grade",
        "region",
        "has_best_answer"
    ], [
        "AND" => [
            "sex" => $sex_ask,
            "visible" => 1
        ]
    ]);
    if (!is_array($data)) {
        return -1;
    } elseif (sizeof($data) == 0) {
        return null;
    } else {
        foreach ($data as $d) {
            //数据塞进对象里
            $qid = $d["id"];
            $title = $d["title"];
            $time_ori = $d["time"];
            $time = date("m-d H:i", strtotime($time_ori));
            $answer_num = $d["answer_num"];

            $grade = $d["grade"];
            $region = $d["region"];
            $hasBestAnswer = $d["has_best_answer"];
            //string 变 boolean
            $hasBestAnswer = $hasBestAnswer == 1 ? true : false;
            $question = new Question($qid, $title, $time, $answer_num, $sex_ask, $grade, $region, $hasBestAnswer);
            $question_list[] = $question;
        }
        return $question_list;
    }
}


/** 设置question表的 已有最佳答案属性
 * @param $qid
 * @param $database
 * @return bool
 */
function setHasBestAnswer($qid, $database)
{
    $table = "qa_question";
    $row = $database->update($table, [
        "has_best_answer" => 1,
        "over_time" => date("Y-m-d H:i:s", time())
    ], [
        "AND" => [
            "id" => $qid,
            "visible" => 1
        ]
    ]);
    //   var_dump($row);

    return $row == 1 ? true : false;
}


/**以qid获取一个 qusetion 对象
 * @param $qid int
 * @param $database
 * @return null|Question object null则报错
 */

function getQuestionByQid($qid, $database)
{
    $table = "qa_question";
    $data = $database->select($table, [
        "title",
        "time", //数据库的time是 Y-m-d H:i:s
        "answer_num",
        "sex",
        "grade",
        "region",
        "has_best_answer"
    ], [
        "AND" => [
            "id" => $qid,
            "visible" => 1
        ]
    ]);
    if ($data == null || !is_array($data)) {
        return null;
    } else {
        foreach ($data as $d) {
            //数据塞进对象里
            $title = $d["title"];
            $time_ori = $d["time"];
            $time = date("m-d H:i", strtotime($time_ori));
            $answer_num = $d["answer_num"];
            $sex = $d["sex"];
            $grade = $d["grade"];
            $region = $d["region"];
            $hasBestAnswer = $d["has_best_answer"];
            //string 变 boolean
            $hasBestAnswer = $hasBestAnswer == 1 ? true : false;

            $question = new Question($qid, $title, $time, $answer_num, $sex, $grade, $region, $hasBestAnswer);
            //var_dump($question);
            return $question;
        }
    }
}

/** 检测是否已经有最佳回答（qid要正确）
 * @param $question_id
 * @param $database
 * @return mixed
 */
function hasBestAnswer($question_id, $database)
{
    $table = "qa_question";
    $has = $database->has($table, [
        "AND" => [
            "id" => $question_id,
            "has_best_answer" => 1,
            "visible" => 1
        ]
    ]);
    return $has;

}


/** 检查是否是自己为题主
 * @param $user_id
 * @param $question_id
 * @param $database
 * @return bool
 */
function isMyQuestion($user_id, $question_id, $database)
{
    $table = "qa_question";
    $has = $database->has($table, [
        "AND" => [
            "id" => $question_id,
            "uid" => $user_id,
            "visible" => 1
        ]
    ]);
    return $has;
}


/**检查qid是否存在且有效
 * @param $question_id
 * @param $database
 * @return boolean
 */

function isVaildQid($question_id, $database)
{
    $table = "qa_question";
    if (!is_numeric($question_id)) {
        return false;
    } else {
        $has = $database->has($table, [
            "AND" => [
                "id" => $question_id,
                "visible" => 1
            ]
        ]);
        return $has;
    }

}


/** 发布问题后 是否成功改该问题的修改回答数
 * @param $qid
 * @param $database
 * @return bool
 */
function changeAnswerNumByQid($qid, $database)
{
    $table = "qa_question";
    $row_affected = $database->update($table, [
        "answer_num[+]" => 1
    ], [
        "AND" => [
            "id" => $qid,
            "visible" => 1
        ]
    ]);
    if (is_numeric($row_affected) && $row_affected == 1) {
        return true;
    } else {
        return false;
    }
}


/** 检查字数限制
 * @param $title
 * @param int $limit
 */

function titleIsInLength($title, $limit = 60)
{
    return (mb_strlen($title, 'UTF-8') < $limit) ? true : false;
}


/** 以 user_id 检查数据库是否已发布过问题 是否有限制
 * @param $user_id int
 * @return boolean true有限制 false没有 null报错
 */

function hasQuestionSubmitLimit($user_id, $database)
{
    $table = "qa_question";
    $data = $database->select($table, [
        "time"
    ], [
        "AND" => [
            "uid" => $user_id,
            "visible" => 1
        ]
        ,
        "ORDER" => [
            "time" => "DESC"
        ]
    ]);
//      print_r($data);
//    echo gettype($data);
    //数组空 == false 判断为真
    if ($data == false) {
        //查不到没发过问题
        return false;
    } else {
        //检查时间
        $latest_time = $data[0]["time"];//自取第一个最晚的时间
        $latest_date = date("Y-m-d", strtotime($latest_time));
        $now_date = date("Y-m-d", time());
        // var_dump($latest_date);
        // var_dump($now_date);
        if ($latest_date == $now_date) {
            //echo "true";
            //已经发过
            return true;
        } else {
            return false;
        }

    }
}

/** 发布问题
 * @param $user_id
 * @param $title
 * @param $database
 * @param $info  array 通过getUserInfoArrayById($user_id, $database)获得的信息字符索引数组
 * @return bool|null
 */

function questionSubmit($user_id, $title, $info, $database)
{
    $table = "qa_question";
    if ($info == null) {
        return null;
    } else {
        $sex = $info["sex"];
        $region = $info["region"];
        $grade = $info["grade"];

        $insert_id = $database->insert($table, [
            "title" => $title,
            "time" => date("Y-m-d H:i:s", time()),
            "answer_num" => 0,
            "uid" => $user_id,
            "sex" => $sex,
            "region" => $region,
            "grade" => $grade,
            "has_best_answer" => 0,
            "visible" => 1
        ]);
        if (is_numeric($insert_id) && $insert_id != 0) {
            return true;
        } else {
            return false;
        }

    }

}