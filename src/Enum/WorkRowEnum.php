<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Enum;

/**
 * Description of ClassroomTypeEnum
 *
 * @author nazar
 */
class WorkRowEnum extends TypeEnum {

    //put your code here
    const CREDITCOURSE = 0;
    const NONCREDITCOURSE = 1;
    const INTERNSHIPCOURSE = 2;
    const THESISADVISING = 3;
    const THESISEXAM = 4;
    const STATEEXAM = 5;
    const COMPETITION = 6;
    const CLASSOBSERVATION = 7;
    const ADMINISTRATION = 8;
    const THESISREVIEW = 9;
    const EXTRACOURSE = 10;
    const COURSEEXAM = 11;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::CREDITCOURSE => 'Credit course',
        self::NONCREDITCOURSE => 'Non-credit course',
        self::INTERNSHIPCOURSE => 'Internship course',
        self::THESISADVISING => 'Thesis advising',
        self::THESISEXAM => 'Thesis exam',
        self::STATEEXAM => 'State exam',
        self::COMPETITION => 'Competition preparation',
        self::CLASSOBSERVATION => 'Class observation',
        self::ADMINISTRATION => 'Administration',
        self::THESISREVIEW => 'Thesis review writing',
        self::EXTRACOURSE => 'Extra course',
        self::COURSEEXAM => 'Course exam'
    ];

}
