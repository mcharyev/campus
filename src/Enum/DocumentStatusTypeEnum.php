<?php

namespace App\Enum;

/**
 * Description of DocumentEntryTypeTypeEnum
 * Used for Electronic Document Management System Module for Campus
 * @author Nazar Mammedov
 */
class DocumentStatusTypeEnum extends TypeEnum {

    //put your code here
    const GENERAL = 1;
    const NEW = 2;
    const REQUESTED = 3;
    const IN_PROCESS = 4;
    const ARCHIVED = 5;
    const HIDDEN = 99;
    const ALL = 100;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::GENERAL => 'Umumy',
        self::NEW => 'Täze',
        self::REQUESTED => 'Talap edildi',
        self::IN_PROCESS => 'Işlenýär',
        self::ARCHIVED => 'Arhiw',
        self::HIDDEN => 'Gizlenen',
        self::ALL => 'Hemmesi',
    ];

}
