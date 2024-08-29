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
class SystemEventTypeEnum extends TypeEnum {

    //put your code here
    const EVENT_NULL = 0;
    const EVENT_CREATE = 1;
    const EVENT_UPDATE = 2;
    const EVENT_DELETE = 3;
    const EVENT_VIEW = 4;
    const EVENT_LOGIN = 5;
    const EVENT_LOGOUT = 6;
    const EVENT_ENABLE = 7;
    const EVENT_DISABLE = 8;
    const EVENT_SHOW = 9;
    const EVENT_HIDE = 10;
    const EVENT_LOGINFAIL = 11;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::EVENT_NULL => 'Null',
        self::EVENT_CREATE => 'Create',
        self::EVENT_UPDATE => 'Update',
        self::EVENT_DELETE => 'Delete',
        self::EVENT_VIEW => 'View',
        self::EVENT_LOGIN => 'Login',
        self::EVENT_LOGOUT => 'Logout',
        self::EVENT_ENABLE => 'Enable',
        self::EVENT_DISABLE => 'Disable',
        self::EVENT_SHOW => 'Show',
        self::EVENT_HIDE => 'Hide',
        self::EVENT_LOGINFAIL => 'Login fail'
    ];

}
