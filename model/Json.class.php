<?php

/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-18
 * Time: 22:59
 */
class Json
{
    public  $retcode;
    public  $retmsg;
    public  $retdata;

    /**
     * Json constructor.
     * @param $retcode
     * @param $retmsg
     * @param $retdata
     */
    public function __construct($retcode, $retmsg, $retdata)
    {
        $this->retcode = $retcode;
        $this->retmsg = $retmsg;
        $this->retdata = $retdata;
    }

}