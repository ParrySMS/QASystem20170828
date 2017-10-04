<?php

/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-22
 * Time: 21:19
 */
class Answer
{
    public $id;
    public $text;
    public $time;
    public $grade;
    public $region;
    public $isBestAnswer;
    public $isMyAnswer;

    /**
     * Answer constructor.
     * @param $id
     * @param $part_text
     * @param $time
     * @param $grade
     * @param $region
     * @param $isBestAnswer
     * @param $isMyAnswer
     */
    public function __construct($id,$text, $time, $grade, $region, $isBestAnswer, $isMyAnswer)
    {
        $this->id = $id;
        $this->text = $text;
        $this->time = $time;
        $this->grade = $grade;
        $this->region = $region;
        $this->isBestAnswer = $isBestAnswer;
        $this->isMyAnswer = $isMyAnswer;
    }

}