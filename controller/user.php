<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-20
 * Time: 16:20
 */

date_default_timezone_set('Asia/Shanghai');

/**获取我目前最佳回答的数量
 * @param $user_id
 * @param $database
 * @return null
 */
function getMyBestAnswerNum($user_id, $database)
{
    $table = "qa_user";
    $data = $database->get($table, [
        "best_answer_num"
    ], [
        "AND" => [
            "id" => $user_id,
            "visible" => 1
        ]
    ]);
    return isset($data["best_answer_num"]) ? $data["best_answer_num"] : null;


}


/**以uid获取用户的性别
 * @param $user_id
 * @param $database
 * @return null
 */
function getUserSexByUid($user_id, $database)
{
    $table = "qa_user";
    $data = $database->select($table, [
        "sex"
    ], [
        "AND" => [
            "id" => $user_id,
            "visible" => 1
        ]
    ]);
    if (!is_array($data) && sizeof($data) != 1) {
        return null;
    } else {
        foreach ($data as $d) {
            $sex = $d["sex"];
            return $sex;
        }
    }
}

/** 用户的最佳回答数增加
 * @param $user_id
 * @param $database
 * @return bool
 */
function addBestAnswerNum($user_id, $database)
{
    $table = "qa_user";
    $row = $database->update($table, [
        "best_answer_num[+]" => 1
    ], [
        "AND" => [
            "id" => $user_id,
            "visible" => 1
        ]
    ]);
    //var_dump($row);
    return $row == 1 ? true : false;
}


/** 检查是否为有效的用户id
 * @param $user_id
 * @param $database
 * @return bool
 */
function isVaildUid($user_id, $database)
{
    $table = "qa_user";
    if (!is_numeric($user_id)) {
        return false;
    } else {
        $has = $database->has($table, [
            "AND" => [
                "id" => $user_id,
                "visible" => 1
            ]
        ]);
        return $has;
    }

}

//
///** 以user_id检查数据里是否已经设置用户信息
// * @param $user_id
// * @param $database
// * @return bool|null true已设置 false未设置 null报错
// */
//function userInfoIsSet($user_id,$database)
//{
//    $table = "qa_user";
//    $select = $database->select($table, [
//        "sex",
//        "region",
//        "grade",
//        "wechat",
//        "phone"
//    ], [
//        "AND" => [
//            "id" => $user_id,
//            "visible" => 1
//        ]
//    ]);
//    if ($select == null || (!is_array($select) || sizeof($select) != 1)) {
//        return null;
//    } else {
//        foreach ($select as $s) {
//            $sex_db = $s["sex"];
//            $region_db = $s["region"];
//            $grade_db = $s["grade"];
//            $wechat_db = $s["wechat"];
//            $phone_db = $s["phone"];
//
//            if ($sex_db != null &&
//                $region_db != null &&
//                $grade_db != null &&
//                $wechat_db != null &&
//                $phone_db != null
//            ) {
//                return true;
//
//            } elseif ($sex_db == null &&
//                $region_db == null &&
//                $grade_db == null &&
//                $wechat_db == null &&
//                $phone_db == null
//            ) {
//                return false;
//            } else {
//                //既不是全空也不是全满
//                return null;
//            }
//        }
//    }
//
//}

/** 将信息入库到指定用户 返回是否成功
 * @param $user_id
 * @param $sex
 * @param $region
 * @param $grade
 * @param $wechat
 * @param $phone
 * @param $database
 * @return bool
 */
function setUserInfo($user_id, $sex, $region, $grade, $wechat, $phone, $database)
{
    $table = "qa_user";
    //$table = "qa_user";
    $data = $database->update($table, [
        "sex" => $sex,
        "region" => $region,
        "grade" => $grade,
        "wechat" => $wechat,
        "phone" => $phone
    ], [
        "AND" => [
            "id" => $user_id,
            "visible" => 1
        ]
    ]);
//    print_r($data);
    return $data == 1 ? true : false;
}


/**
 * 以openid换取user_id
 * @param string $openid 微信用户的openid
 * @param object $database 数据库
 * @return int $user_id 用户id
 * @author Parry < yh@szer.me >
 */
function getUseridByOpenid($openid, $database)
{
    $table = "qa_user";
    $data = $database->select($table, [
        "id",
        "visible"
    ], [
        "AND" => [
            "openid" => $openid,
        ]
    ]);
    if ($data == null) {
        $user_id = getUseridByCreatingUser($database, $openid);
        return $user_id;
    } else {
        foreach ($data as $d) {
            //检验是否被封号
            if ($d["visible"] == 0) {
                return -1;
            } else {
                $user_id = $d['id'];
                return $user_id;
            }
        }
    }
}

/**
 * 以openid 创建新用户 并返回user_id
 * @param object $database 数据库
 * @param string $openid 微信openid
 * @return int $user_id 用户id null 报错
 * @author Parry < yh@szer.me >
 */
function getUseridByCreatingUser($database, $openid)
{
    $table = "qa_user";
    $insert_id = $database->insert($table, [
        "openid" => $openid,
        "time" => date("Y-m-d H:i:s"),
        "visible" => 1
    ]);
    if (is_numeric($insert_id) && $insert_id != 0) {
        return $insert_id;
    } else {
        return null;
    }

}


/**以user_id 获取用户信息数组
 * @param $user_id
 * @return array|null null报错 正常返回字符关联数组
 */
function getUserInfoArrayById($user_id, $database)
{
    $table = "qa_user";
    $data = $database->select($table, [
        "sex",
        "region",
        "grade",
        "wechat",
        "phone"
    ], [
        "AND" => [
            "id" => $user_id,
            "visible" => 1
        ]
    ]);
    if ($data == null || !is_array($data) || sizeof($data) != 1) {
        return null;
    } else {
        foreach ($data as $d) {
            foreach ($d as $value) {
                if ($value == null || $value == '') {
                    return null;
                }
            }
            //检查完非空
            return $d;
        }
    }
}


/**
 * 以user_id获取是否在数据库中已经设置用户信息
 * @param  string $user_id 用户id
 * @return boolean  null则无用户，false信息不全，true 已设置
 * @author Parry < yh@szer.me >
 */

function userSettingIsDone($user_id, $database)
{
    $table = "qa_user";
    $data = $database->select($table, [
        "sex",
        "region",
        "grade",
        "wechat",
        "phone"
    ], [
        "AND" => [
            "id" => $user_id,
            "visible" => 1
        ]
    ]);
//    echo $user_id;
    //  print_r($data);
    if (empty($data) || !is_array($data)) {
        return null;
    } else {
        foreach ($data as $d) {
            foreach ($d as $dd) {
                if (is_null($dd)) {
//                    echo "data null";
                    return false;
                }
            }
        }
        //遍历结束 全部非空
        return true;
    }
}