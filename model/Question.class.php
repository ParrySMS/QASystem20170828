<?php

/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-22
 * Time: 20:19
 */
class Question
{
    public $id;
    public $title;
    public $time;
    public $answer_num;
    public $sex;
    public $grade;
    public $region;
    public $hasBestAnswer;

    /**
     * Question constructor.
     * @param $id
     * @param $title
     * @param $time
     * @param $answer_num
     * @param $sex
     * @param $grade
     * @param $region
     * @param $hasBestAnswer
     */
    public function __construct($id, $title, $time, $answer_num, $sex, $grade, $region, $hasBestAnswer)
    {
        $this->id = $id;
        $this->title = $title;
        $this->time = $time;
        $this->answer_num = $answer_num;
        $this->sex = $sex;
        $this->grade = $grade;
        $this->region = $region;
        $this->hasBestAnswer = $hasBestAnswer;
    }


}