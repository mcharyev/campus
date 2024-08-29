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
class ClassTypeEnum extends TypeEnum {

    //put your code here
    const LECTURE = 1;
    const SEMINAR = 2;
    const PRACTICE = 3;
    const LAB = 4;
    const CLASSHOUR = 5;
    const SIWSI = 6;
    const LANGUAGE = 7;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::LECTURE => 'Lecture',
        self::SEMINAR => 'Seminar',
        self::PRACTICE => 'Practice',
        self::LAB => 'Laboratory',
        self::CLASSHOUR => 'Class hour',
        self::SIWSI => 'Students independent work',
        self::LANGUAGE => 'Language Class',
    ];

}
