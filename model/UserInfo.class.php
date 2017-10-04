<?php

/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-25
 * Time: 12:07
 */
class UserInfo
{
   //public $id;
    public $sex;
    public $region;
    public $grade;
    public $wechat;
    public $phone;

    /**
     * UserInfo constructor.
     * @param $id
     * @param $sex
     * @param $region
     * @param $grade
     * @param $wechat
     * @param $phone
     */
    public function __construct($sex, $region, $grade, $wechat, $phone)
    {
        //$this->id = $id;
        $this->sex = $sex;
        $this->region = $region;
        $this->grade = $grade;
        $this->wechat = $wechat;
        $this->phone = $phone;
    }


}