<?php

namespace App\Enum;

/**
 * Description of DocumentEntryTypeTypeEnum
 * Used for Electronic Document Management System Module for Campus
 * @author Nazar Mammedov
 */
class DocumentEntryTypeEnum extends TypeEnum {

    //put your code here
    const INCOMING = 1;
    const OUTGOING = 2;
    const INTERNAL = 3;
    const OTHER = 100;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::INCOMING => 'Gelýän hat',
        self::OUTGOING => 'Gidýän hat',
        self::INTERNAL => 'Içerki hat',
        self::OTHER => 'Başga',
    ];

}
