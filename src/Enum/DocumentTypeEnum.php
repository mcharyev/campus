<?php

namespace App\Enum;

/**
 * Description of ClassroomTypeEnum
 * Used for Electronic Document Management System Module for Campus
 * @author Nazar Mammedov
 */
class DocumentTypeEnum extends TypeEnum {

    //put your code here
    const GENERAL = 1;
    const PETITION = 2;
    const MINISTRYORDER = 3;
    const TENDER = 50;
    const CONTRACT = 51;
    const INVOICE = 52;
    const OTHER = 100;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::GENERAL => 'Umumy',
        self::MINISTRYORDER => 'Ministriň buýrugy',
        self::PETITION => 'Arza',
        self::TENDER => 'Tender',
        self::CONTRACT => 'Şertnama',
        self::INVOICE => 'Inwoýs',
        self::OTHER => 'Başga',
    ];

}
