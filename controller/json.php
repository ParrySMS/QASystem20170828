<?php

require "../model/Json.class.php";

/**
 * 获得可直接发送的Json
 * @param int $retcode 错误码
 * @param string $retmsg 报错信息
 * @param object $retdata 返回数据信息
 * @author Parry < yh@szer.me >
 */

function JsonPrint($retcode, $retmsg, $retdata)
{
    switch ($retcode) {
        case 200:
            success($retdata);
            break;
        case 400:
            error400($retmsg);
            break;
        case 401:
            error401($retmsg);
			break;
        case 402:
            error402($retmsg);
            break;
        case 403:
            error403($retmsg);
            break;
        case 404:
            error404($retmsg);
            break;
        case 406:
            error406($retmsg);
            break;
        case 420:
            error420($retmsg);
            break;
        case 421:
            error421($retmsg);
            break;
        case 500:
            error500($retmsg);
            break;
        default:
            return null;
    }

}

//200成功
function success($retdata)
{
    $json = new Json(200, null, $retdata);
    print_r( json_encode($json));
}

//400报错
function error400($retmsg)
{
    $json = new Json(400, $retmsg, null);
    print_r( json_encode($json));

}

//401未授权
function error401($retmsg)
{
    $json = new Json(401, $retmsg, null);
    print_r( json_encode($json));
}

//402过期已结束
function error402($retmsg)
{
    $json = new Json(402, $retmsg, null);
    print_r( json_encode($json));
}

//403报错 拒绝执行
function error403($retmsg)
{
    $json = new Json(403, $retmsg, null);
    print_r( json_encode($json));
}
//404报错 逻辑不应该存在的情况
function error404($retmsg)
{
    $json = new Json(404, $retmsg, null);
    print_r( json_encode($json));
}
//406报错 表示Not Acceptable 不可接受 无法完成
function error406($retmsg)
{
    $json = new Json(406, $retmsg, null);
    print_r( json_encode($json));
}
//色情不健康内容
function error420($retmsg)
{
    $json = new Json(420, $retmsg, null);
    print_r( json_encode($json));
}
//政治敏感
function error421($retmsg)
{
    $json = new Json(421, $retmsg, null);
    print_r( json_encode($json));
}

//500报错
function error500($retmsg)
{
    $json = new Json(500, $retmsg, null);
    print_r(json_encode($json));
}

