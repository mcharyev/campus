<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Enum;

/**
 * Description of GradedCourseTypeEnum
 *
 * @author nazar
 */
class GradedCourseTypeEnum extends TypeEnum {

    //put your code here
    const COURSE = 1;
    const INTERNSHIP = 2;
    const THESIS = 3;
    const EXAM = 4;
    const DEFENSE = 5;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::COURSE => 'Standard course',
        self::INTERNSHIP => 'Work internship',
        self::THESIS => 'Research practice',
        self::EXAM => 'State examination',
        self::DEFENSE => 'Thesis defense',
    ];

}