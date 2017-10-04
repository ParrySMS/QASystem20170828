<?php

/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-22
 * Time: 23:25
 */
class AnswerList
{
    public $isMyQuestion;
    public $questionObj;
    public $answerList=array();

    /**
     * AnswerList constructor.
     * @param $isMyQuestion
     * @param $questionObj
     * @param array $answerList
     */
    public function __construct($isMyQuestion, $questionObj, array $answerList)
    {
        $this->isMyQuestion = $isMyQuestion;
        $this->questionObj = $questionObj;
        $this->answerList = $answerList;
    }

}