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
class TurkmenMonthsEnum extends TypeEnum {

    //put your code here
    const JANUARY = 1;
    const FEBRUARY = 2;
    const MARCH = 3;
    const APRIL = 4;
    const MAY = 5;
    const JUNE = 6;
    const JULY = 7;
    const AUGUST = 8;
    const SEPTEMBER = 9;
    const OCTOBER = 10;
    const NOVEMBER = 11;
    const DECEMBER = 12;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::JANUARY => 'ýanwar',
        self::FEBRUARY => 'fewral',
        self::MARCH => 'mart',
        self::APRIL => 'aprel',
        self::MAY => 'maý',
        self::JUNE => 'iýun',
        self::JULY => 'iýul',
        self::AUGUST => 'awgust',
        self::SEPTEMBER => 'sentýabr',
        self::OCTOBER => 'oktýabr',
        self::NOVEMBER => 'noýabr',
        self::DECEMBER => 'dekabr',
    ];

}
