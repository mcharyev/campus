<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Enum;

/**
 * Description of IncludeColumnEnum
 *
 * @author nazar
 */
class IncludeColumnEnum extends TypeEnum {

    //put your code here
    const ALL = 0;
    const LECTURE_ONLY = 1;
    const PRACTICE_ONLY = 2;
    const LAB_ONLY = 3;
    const EXAMS_ONLY = 4;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::ALL => 'All',
        self::LECTURE_ONLY => 'Lecture',
        self::PRACTICE_ONLY => 'Practice',
        self::LAB_ONLY => 'Lab',
        self::EXAMS_ONLY => 'Exams'
    ];

}
