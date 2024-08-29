<?php

namespace App\Enum;

/**
 * Description of DocumentOriginTypeEnum
 * Used for Electronic Document Management System Module for Campus
 * @author Nazar Mammedov
 */
class DocumentOriginTypeEnum extends TypeEnum {

    //put your code here
    const PHYSICAL_PERSON = 1;
    const LEGAL_PERSON = 2;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::PHYSICAL_PERSON => 'Şahsy tarap',
        self::LEGAL_PERSON => 'Edara görnüşli tarap',
    ];

}
