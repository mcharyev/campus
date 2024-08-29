<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Enum;

/**
 * Description of GradedCourseTypeEnum
 *
 * @author nazar
 */
class GradedCourseCreditTypeEnum extends TypeEnum {

    //put your code here
    const CREDIT = 1;
    const NONCREDIT = 2;
    const GRADEDNONCREDIT = 3;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::CREDIT => 'Credit',
        self::NONCREDIT => 'Non-credit',
        self::GRADEDNONCREDIT => 'Graded non-credit',
    ];

}
