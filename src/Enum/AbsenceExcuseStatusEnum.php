<?php

namespace App\Enum;

/**
 * Description of ClassroomTypeEnum
 *
 * @author nazar
 */
class AbsenceExcuseStatusEnum extends TypeEnum {

    //put your code here
    const UNEXCUSED = 0;
    const EXCUSED = 1;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::UNEXCUSED => 'Unexcused',
        self::EXCUSED => 'Excused'
    ];

}
