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
class UserTypeEnum extends TypeEnum {

    //put your code here
    const USER_DISABLED = 0;
    const USER_STUDENT = 1;
    const USER_TEACHER = 2;
    const USER_LIBRARY = 3;
    const USER_HR = 4;
    const USER_FACILITIES = 5;
    const USER_ADMINISTRATION = 6;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::USER_DISABLED => "Inactive user",
        self::USER_STUDENT => 'Student',
        self::USER_TEACHER => 'Teacher',
        self::USER_LIBRARY => 'Librarian',
        self::USER_HR => 'HR',
        self::USER_FACILITIES => 'Facilities Staff',
        self::USER_ADMINISTRATION => 'Administration'
    ];

}
