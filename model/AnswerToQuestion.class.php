<?php

/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-23
 * Time: 13:57
 */
class AnswerToQuestion
{
    public $isMyQuestion ;
    public $questionObj;
    public $answerObj;

    /**
     * AnswerToQuestion constructor.
     * @param $isMyQuestion bool
     * @param $questionObj object
     * @param $answerObj object
     */
    public function __construct($isMyQuestion, $questionObj, $answerObj)
    {
        $this->isMyQuestion = $isMyQuestion;
        $this->questionObj = $questionObj;
        $this->answerObj = $answerObj;
    }


}