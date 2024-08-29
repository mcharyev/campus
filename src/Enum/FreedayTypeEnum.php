<?php

namespace App\Enum;

/**
 * Description of FreedayTypeEnum
 *
 * @author nazar
 */
class FreedayTypeEnum extends TypeEnum {

    //put your code here
    const REPLACEMENT = 1; //this is a replacement for the entire day
    const NOREPLACEMENT = 2;
    const SESSION_REPLACEMENT = 3; //this is a replacement for session by session
    const NOREPLACEMENT_LLD = 4;
    const SESSION_REPLACEMENT_BM = 5; // this is session by session replacement for Bachelor and masters

    /** @var array user friendly named type */
    protected static $typeName = [
        self::REPLACEMENT => 'Day replacement Bachelor Master',
        self::NOREPLACEMENT => 'No replacement Bachelor Master',
        self::SESSION_REPLACEMENT_BM => 'Session replacement Bachelor Master',
        self::SESSION_REPLACEMENT => 'Session replacement LLD',
        self::NOREPLACEMENT_LLD => 'No replacement LLD'
    ];

}
