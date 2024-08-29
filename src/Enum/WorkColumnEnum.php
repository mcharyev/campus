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
class WorkColumnEnum extends TypeEnum {

    //put your code here
    const NUMBER = 1;
    const TITLE = 2;
    const GROUPNAMES = 3;
    const STUDYYEAR = 4;
    const STUDENTCOUNT = 5;
    const COHORT = 6;
    const GROUPS = 7;
    const SUBGROUPS = 8;
    const LECTUREPLAN = 9;
    const LECTUREACTUAL = 10;
    const PRACTICEPLAN = 11;
    const PRACTICEACTUAL = 12;
    const LABPLAN = 13;
    const LABACTUAL = 14;
    const CONSULTATION = 15;
    const NONCREDIT = 16;
    const MIDTERMEXAM = 17;
    const FINALEXAM = 18;
    const STATEEXAM = 19;
    const SIWSI = 20;
    const CUSW = 21;
    const INTERNSHIP = 22;
    const THESISADVISING = 23;
    const THESISEXAM = 24;
    const COMPETITION = 25;
    const CLASSOBSERVATION = 26;
    const ADMINISTRATION = 27;
    const THESISREVIEW = 28;
    const TOTAL = 29;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::NUMBER => 'T/b',
        self::TITLE => 'Ady',
        self::GROUPNAMES => 'Toparlar',
        self::STUDYYEAR => 'Okuw ýyly',
        self::STUDENTCOUNT => 'Talyp sany',
        self::COHORT => 'Tapgyr sany',
        self::GROUPS => 'Topar sany',
        self::SUBGROUPS => 'Toparça sany',
        self::LECTUREPLAN => 'Meýilnama boýunça',
        self::LECTUREACTUAL => 'Jemi',
        self::PRACTICEPLAN => 'Meýilnama boýunça',
        self::PRACTICEACTUAL => 'Jemi',
        self::LABPLAN => 'Meýilnama boýunça',
        self::LABACTUAL => 'Jemi',
        self::CONSULTATION => 'Berkidiji sapak',
        self::NONCREDIT => 'Hasap',
        self::MIDTERMEXAM => 'Arasynag',
        self::FINALEXAM => 'Jemleýji synag',
        self::STATEEXAM => 'Döwlet synagy',
        self::SIWSI => 'MÝTÖI',
        self::CUSW => 'Tabşyryklary barlamak',
        self::INTERNSHIP => 'Önümçilik tejribeligine ýolbaşçylyk',
        self::THESISADVISING => 'Diplom işine ýolbaşçylyk',
        self::THESISEXAM => 'Diplom işini goramak',
        self::COMPETITION => 'Ders bäsleşiklerine taýýarlamak',
        self::CLASSOBSERVATION => 'Sapaklara gatnaşmak',
        self::ADMINISTRATION => 'Fakultete/kafedra ýolbaşçylyk',
        self::THESISREVIEW => 'Syn ýazmak',
        self::TOTAL => 'Jemi',
    ];

}
