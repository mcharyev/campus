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
class ClassroomTypeEnum extends TypeEnum {
    //put your code here
    const LECTURE_ROOM    = 1;
    const SEMINAR_ROOM = 2;
    const LAB_ROOM = 3;
    const SPORT_ROOM = 4;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::LECTURE_ROOM    => 'Lecture',
        self::SEMINAR_ROOM => 'Seminar',
        self::LAB_ROOM => 'Laboratory',
        self::SPORT_ROOM => 'Sport room'
    ];
}
