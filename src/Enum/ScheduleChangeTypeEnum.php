<?php

namespace App\Enum;

/**
 * Description of ScheduleChangeTypeEnum
 *
 * @author nazar
 */
class ScheduleChangeTypeEnum extends TypeEnum {

    //put your code here
    const NONE = 0;
    const REPLACEMENT = 1;
    const FREEDAY = 2;
    const FREEDAYLLD = 3;
    const SESSION_REPLACEMENT = 4;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::NONE => 'None',
        self::REPLACEMENT => 'Replacement',
        self::FREEDAY => 'Freeday Bachelor',
        self::FREEDAYLLD => 'Freeday LLD',
        self::SESSION_REPLACEMENT => 'Session replacement',
    ];

}
