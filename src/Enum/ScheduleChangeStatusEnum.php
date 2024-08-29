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
class ScheduleChangeStatusEnum extends TypeEnum {

    //put your code here
    const UNLOCKED = 0;
    const LOCKED = 1;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::UNLOCKED => 'Unlocked',
        self::LOCKED => 'Locked',
    ];

}
