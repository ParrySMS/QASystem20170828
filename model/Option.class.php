<?php

/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-20
 * Time: 1:22
 */
class Option
{
    public $option_region = array();
    public $option_grade = array();

    /**
     * Option constructor.
     * @param array $option_region
     * @param array $option_grade
     */
    public function __construct(array $option_region, array $option_grade)
    {
        $this->option_region = $option_region;
        $this->option_grade = $option_grade;
    }

}