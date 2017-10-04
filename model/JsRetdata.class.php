<?php

/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-24
 * Time: 18:55
 */
class JsRetdata
{
    public $timestamp;
    public $nonceStr;
    public $signature;

    /**
     * JsRetdata constructor.
     * @param $timestamp
     * @param $nonceStr
     * @param $signature
     */
    public function __construct($timestamp, $nonceStr, $signature)
    {
        $this->timestamp = $timestamp;
        $this->nonceStr = $nonceStr;
        $this->signature = $signature;
    }

}