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
class AbsenceStatusEnum extends TypeEnum {

    //put your code here
    const NULL = 0;
    const TEACHER_APPROVED = 1;
    const DEAN_APPROVED = 2;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::NULL => 'Null',
        self::TEACHER_APPROVED => 'Teacher approved',
        self::DEAN_APPROVED => 'Dean approved',
    ];

}
