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
class WorkloadEnum extends TypeEnum {

    //put your code here
    const LOAD100 = 0;
    const LOAD075 = 1;
    const LOAD050 = 2;
    const LOAD025 = 3;
    const LOADOTHER = 4;
    const LOADHOURLY = 5;
    const LOADREPLACEMENT = 6;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::LOAD100 => '1.00',
        self::LOAD075 => '0.75',
        self::LOAD050 => '0.50',
        self::LOAD025 => '0.25',
        self::LOADOTHER => 'other',
        self::LOADHOURLY => 'hourly',
        self::LOADREPLACEMENT => 'replacement',
    ];

}
